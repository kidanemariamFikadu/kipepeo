<?php

use App\Models\Attendance;
use App\Models\AttendanceAttr;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\BookCopy;
use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\Rental;
use App\Models\School;
use App\Models\SchoolStudent;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\User;
use App\Models\Volunteer;
use App\Models\VolunteerAttendance;
use Illuminate\Support\Facades\DB;

/**
 * Builds a realistic slice of the app at a given size: every student has a
 * school, a grade, a guardian, attendance and a book on loan, so a view that
 * walks any of those relations per row shows up as extra queries.
 */
function seedScale(int $size): void
{
    $category = BookCategory::create(['name' => 'Fiction']);
    $schools = collect(range(1, max(1, intdiv($size, 2))))
        ->map(fn ($i) => School::create(['name' => "School {$i}"]));
    $grades = collect(range(1, 3))->map(fn ($i) => Grade::create(['grade' => "Grade {$i}"]));
    $books = collect(range(1, $size))->map(fn ($i) => Book::create([
        'title' => "Book {$i}", 'author' => "Author {$i}", 'publisher' => 'P',
        'copies' => 2, 'category' => $category->id,
    ]));

    foreach ($books as $book) {
        BookCopy::create(['book_id' => $book->id, 'status' => 'available', 'copy_number' => 1]);
    }

    $staff = User::factory()->create(['role' => 'user']);

    foreach (range(1, $size) as $i) {
        $student = Student::create([
            'name' => "Student {$i} Surname", 'dob' => '2013-04-02',
            'gender' => $i % 2 ? 'male' : 'female',
        ]);

        SchoolStudent::create(['student_id' => $student->id, 'school_id' => $schools[$i % $schools->count()]->id, 'is_current' => true]);
        GradeStudent::create(['student_id' => $student->id, 'grade' => $grades[$i % 3]->id, 'is_current' => true]);
        StudentGuardian::create(['student_id' => $student->id, 'guardian_name' => "Guardian {$i}", 'guardian_phone' => '0700000000', 'is_primary' => true]);

        $attendance = Attendance::create(['student_id' => $student->id, 'date' => now()->toDateString(), 'current_in' => true, 'total_time' => 1800]);
        AttendanceAttr::create(['attendance_id' => $attendance->id, 'student_id' => $student->id, 'date' => now()->toDateString(), 'time_in' => '08:00']);

        Rental::create([
            'book_id' => $books[$i % $books->count()]->id, 'student_id' => $student->id,
            'user_id' => $staff->id, 'rented_at' => now()->subDays(30),
            'due_at' => now()->subDays(16), 'returned_at' => null,
        ]);

        $volunteer = Volunteer::create(['name' => "Volunteer {$i}", 'phone' => '070', 'email' => "v{$i}@example.com", 'status' => 'active']);
        VolunteerAttendance::create(['volunteer_id' => $volunteer->id, 'date' => now()->toDateString(), 'current_in' => true, 'total_time' => 900]);
    }
}

function auditedScreens(): array
{
    return [
        '/dashboard', '/students', '/attendance', '/volunteers', '/books',
        '/users', '/settings/schools', '/settings/volunteers', '/settings/grades',
        '/reports/enrollment', '/reports/attendance-analytics', '/reports/attendance-roster',
        '/reports/book-rental', '/reports/grade-distribution', '/reports/alumni',
        '/reports/volunteer',
    ];
}

/** @return array<string, int> queries per screen */
function queryCounts(int $size, $test): array
{
    seedScale($size);
    $admin = User::factory()->create(['role' => 'admin']);

    // AuthenticateSession ties a session to the user's password hash, so a
    // session carried over from the previous size logs itself out and every
    // request comes back 302.
    $test->flushSession();

    $counts = [];

    // The query LOG, not DB::listen: a listener registered inside this loop
    // would stay registered, so screen N would count every query N times --
    // which is exactly how the first version of this audit "proved" there
    // were no N+1s.
    DB::enableQueryLog();

    foreach (auditedScreens() as $path) {
        DB::flushQueryLog();

        $test->actingAs($admin)->get($path)->assertOk();

        $counts[$path] = count(DB::getQueryLog());
    }

    DB::disableQueryLog();

    // Guard against a vacuous pass: if the second phase did not really hold
    // more rows, identical query counts would prove nothing.
    $counts['__students'] = Student::count();
    $counts['__attendances'] = Attendance::count();
    $counts['__rentals'] = Rental::count();

    return $counts;
}

test('PERF: query counts do not grow with the number of records', function () {
    // The definitive N+1 test: seed small, seed large, compare. A screen whose
    // query count tracks the row count is walking a relation per row.
    $small = queryCounts(4, $this);

    // Fresh database for the second size.
    $this->artisan('migrate:fresh');
    $large = queryCounts(40, $this);

    // The harness is only meaningful if the second phase really was bigger.
    expect($small['__students'])->toBe(4);
    expect($large['__students'])->toBe(40);
    expect($large['__attendances'])->toBeGreaterThan($small['__attendances']);
    expect($large['__rentals'])->toBeGreaterThan($small['__rentals']);

    $rows = [];
    $offenders = [];

    foreach (auditedScreens() as $path) {
        $growth = $large[$path] - $small[$path];
        $rows[] = sprintf('%-36s %3d -> %3d  (+%d)', $path, $small[$path], $large[$path], $growth);

        // 36 more records. Anything growing by more than a handful is
        // per-row work rather than a few extra lookups.
        if ($growth > 5) {
            $offenders[] = sprintf('%s: %d -> %d queries (+%d for 36 more records)', $path, $small[$path], $large[$path], $growth);
        }
    }

    dump($rows);

    expect($offenders)->toBe([]);
});

test('PERF: modal components do not N+1 either', function () {
    // Modals are opened by event, never routed, so a sweep over URLs cannot
    // see them. The rent modal read Book::available_copies twice per row and
    // that accessor issues two COUNTs each time, so ten books cost forty
    // queries -- invisible to every route-based check.
    $modals = [
        App\Livewire\Attendance\QuickCheckInStudents::class,
        App\Livewire\Attendance\QuickCheckInVolunteers::class,
        App\Livewire\Book\CreateBook::class,
        App\Livewire\Book\Rent::class,
        App\Livewire\Setting\ActivityType::class,
        App\Livewire\Setting\BookCategory::class,
        App\Livewire\Setting\Grade::class,
        App\Livewire\Setting\JobTitle::class,
        App\Livewire\Setting\School::class,
        App\Livewire\Setting\Volunteer::class,
        App\Livewire\Student\CreateStudent::class,
        App\Livewire\User\CreateUser::class,
    ];

    $measure = function (int $size) use ($modals) {
        $this->artisan('migrate:fresh');
        seedScale($size);
        $admin = User::factory()->create(['role' => 'admin']);

        $counts = [];
        DB::enableQueryLog();

        foreach ($modals as $modal) {
            DB::flushQueryLog();
            Livewire\Livewire::actingAs($admin)->test($modal)->html();
            $counts[$modal] = count(DB::getQueryLog());
        }

        DB::disableQueryLog();

        return $counts;
    };

    $small = $measure(4);
    $large = $measure(40);

    $offenders = [];

    foreach ($modals as $modal) {
        $growth = $large[$modal] - $small[$modal];

        if ($growth > 2) {
            $offenders[] = sprintf('%s: %d -> %d queries (+%d for 36 more records)', $modal, $small[$modal], $large[$modal], $growth);
        }

        // A modal that renders a list should not be issuing a query per row
        // even at one size.
        if ($large[$modal] > 12) {
            $offenders[] = sprintf('%s: %d queries to open one modal', $modal, $large[$modal]);
        }
    }

    expect($offenders)->toBe([]);
});
