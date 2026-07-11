<?php

use App\Models\ActivityType;
use App\Models\Attendance;
use App\Models\AttendanceAttr;
use App\Models\Book;
use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\Rental;
use App\Models\School;
use App\Models\SchoolStudent;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\User;
use App\Models\Volunteer;
use App\Models\VolunteerActivity;
use App\Models\VolunteerAttendance;
use Illuminate\Support\Facades\DB;

function makeStudentWithRelations(string $name): Student
{
    $student = Student::create(['name' => $name, 'dob' => '2010-01-01', 'gender' => 'male']);

    StudentGuardian::create(['student_id' => $student->id, 'guardian_name' => 'G1', 'guardian_phone' => '111']);
    StudentGuardian::create(['student_id' => $student->id, 'guardian_name' => 'G2', 'guardian_phone' => '222']);

    foreach (['School A', 'School B'] as $schoolName) {
        $school = School::create(['name' => $schoolName]);
        SchoolStudent::create(['student_id' => $student->id, 'school_id' => $school->id, 'is_current' => false]);
    }

    foreach (['GRADE 1', 'GRADE 2'] as $gradeName) {
        $grade = Grade::create(['grade' => $gradeName]);
        GradeStudent::create(['student_id' => $student->id, 'grade' => $grade->id, 'is_current' => false]);
    }

    return $student;
}

test('student detail page shows the requested student, not an unrelated one', function () {
    $user = User::factory()->create(['role' => 'user']);
    $first = makeStudentWithRelations('Alpha Student');
    $second = makeStudentWithRelations('Beta Student');

    $response = $this->actingAs($user)->get("/student-detail/{$second->id}");

    $response->assertOk();
    $response->assertSee('Beta Student');
    $response->assertDontSee('Alpha Student');
});

test('student detail page does not N+1 query per related row', function () {
    $user = User::factory()->create(['role' => 'user']);
    $student = makeStudentWithRelations('Query Count Student');

    $queryCount = 0;
    DB::listen(function () use (&$queryCount) {
        $queryCount++;
    });

    $this->actingAs($user)->get("/student-detail/{$student->id}")->assertOk();

    // 2 guardians + 2 schools (with school) + 2 grades (with gradeTable) + volunteer
    // activities + attendances (with attrs) + rentals (with book) all eager-loaded
    // should stay well under a per-row query count; N+1 would scale with row count instead.
    expect($queryCount)->toBeLessThan(25);
});

test('a graduated badge is shown for a graduated student', function () {
    $user = User::factory()->create(['role' => 'user']);
    $student = makeStudentWithRelations('Graduated Student');
    $student->update(['graduated_at' => '2026-06-01']);

    $response = $this->actingAs($user)->get("/student-detail/{$student->id}");

    $response->assertSee('Graduated 2026-06-01');
});

test('no graduated badge is shown for an active student', function () {
    $user = User::factory()->create(['role' => 'user']);
    $student = makeStudentWithRelations('Active Student');

    $response = $this->actingAs($user)->get("/student-detail/{$student->id}");

    $response->assertDontSee('Graduated ');
});

test('student detail page shows volunteer activity history for the student', function () {
    $user = User::factory()->create(['role' => 'user']);
    $student = makeStudentWithRelations('Tutored Student');
    $volunteer = Volunteer::create(['name' => 'Helpful Volunteer', 'status' => 'active']);
    $activityType = ActivityType::create(['name' => 'Tutoring']);
    $attendance = VolunteerAttendance::create(['volunteer_id' => $volunteer->id, 'date' => now(), 'current_in' => false]);
    $activity = VolunteerActivity::create([
        'volunteer_attendance_id' => $attendance->id,
        'volunteer_id' => $volunteer->id,
        'activity_type_id' => $activityType->id,
        'date' => now(),
        'notes' => 'Great progress',
    ]);
    $activity->students()->attach($student->id);

    $response = $this->actingAs($user)->get("/student-detail/{$student->id}");

    $response->assertSee('Tutoring');
    $response->assertSee('Helpful Volunteer');
});

test('student detail page shows attendance history for the student', function () {
    $user = User::factory()->create(['role' => 'user']);
    $student = makeStudentWithRelations('Attending Student');

    $attendance = Attendance::create([
        'student_id' => $student->id,
        'date' => '2026-06-01',
        'current_in' => false,
        'total_time' => 3661,
    ]);
    AttendanceAttr::create([
        'attendance_id' => $attendance->id,
        'student_id' => $student->id,
        'date' => '2026-06-01',
        'time_in' => '2026-06-01 08:00:00',
        'time_out' => '2026-06-01 09:00:00',
    ]);

    $response = $this->actingAs($user)->get("/student-detail/{$student->id}");

    $response->assertSee('Attendance History');
    $response->assertSee('2026-06-01');
    $response->assertSee('01:01:01');
});

test('student detail page shows book rental history and a return action for the student', function () {
    $user = User::factory()->create(['role' => 'user']);
    $student = makeStudentWithRelations('Borrowing Student');
    $book = Book::create(['title' => 'The Rented Book', 'author' => 'An Author', 'copies' => 2, 'available_copies' => 1]);
    $rental = Rental::create([
        'book_id' => $book->id,
        'student_id' => $student->id,
        'user_id' => $user->id,
        'rented_at' => now(),
        'due_at' => now()->addDays(7),
    ]);

    $response = $this->actingAs($user)->get("/student-detail/{$student->id}");

    $response->assertSee('Book Rentals');
    $response->assertSee('The Rented Book');
    $response->assertSee('Borrowed');
    $response->assertSee("rentalId: {$rental->id}", false);
});

test('a returned rental shows the Returned status and no return action', function () {
    $user = User::factory()->create(['role' => 'user']);
    $student = makeStudentWithRelations('Returned Book Student');
    $book = Book::create(['title' => 'The Returned Book', 'author' => 'An Author', 'copies' => 2, 'available_copies' => 2]);
    Rental::create([
        'book_id' => $book->id,
        'student_id' => $student->id,
        'user_id' => $user->id,
        'rented_at' => now()->subDays(10),
        'due_at' => now()->subDays(3),
        'returned_at' => now()->subDays(2),
    ]);

    $response = $this->actingAs($user)->get("/student-detail/{$student->id}");

    $response->assertSee('Returned');
    $response->assertDontSee('Return this book', false);
});
