<?php

use App\Livewire\Attendance\AttendanceStudent;
use App\Livewire\Attendance\AttendanceVolunteer;
use App\Livewire\Attendance\QuickCheckInStudents;
use App\Livewire\Attendance\QuickCheckInVolunteers;
use App\Livewire\Setting\PromoteStudents;
use App\Models\Attendance;
use App\Models\AttendanceAttr;
use App\Models\Book;
use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\Student;
use App\Models\User;
use App\Models\Volunteer;
use App\Models\VolunteerAttendance;
use App\Models\VolunteerAttendanceAttr;
use Livewire\Livewire;

/* ---------------------------------------------------------------- promote */

test('the promote confirmation sits on the element that carries the action', function () {
    // Livewire reads wire:confirm only from the element holding the action.
    // It used to live on the submit button while wire:submit was on the form,
    // so no dialog ever appeared and one click re-graded every student.
    $admin = User::factory()->create(['role' => 'admin']);
    $grade = Grade::create(['grade' => 'GRADE 1']);
    $next = Grade::create(['grade' => 'GRADE 2']);
    $grade->update(['next_grade_id' => $next->id]);

    $student = Student::create(['name' => 'Rising Student', 'dob' => '2014-01-01', 'gender' => 'male']);
    GradeStudent::create(['student_id' => $student->id, 'grade' => $grade->id, 'is_current' => true]);

    $html = Livewire::actingAs($admin)
        ->test(PromoteStudents::class)
        ->set('selectedGrades', [$grade->id])
        ->html();

    expect($html)->toMatch('/<form[^>]*wire:confirm=/');
    expect($html)->not->toMatch('/<button[^>]*wire:confirm=/');
});

test('the promote confirmation names how many students move and how many graduate', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $promoting = Grade::create(['grade' => 'GRADE 1']);
    $next = Grade::create(['grade' => 'GRADE 2']);
    $promoting->update(['next_grade_id' => $next->id]);
    $terminal = Grade::create(['grade' => 'GRADE 12']);

    foreach (range(1, 3) as $i) {
        $s = Student::create(['name' => "Rising {$i}", 'dob' => '2014-01-01', 'gender' => 'male']);
        GradeStudent::create(['student_id' => $s->id, 'grade' => $promoting->id, 'is_current' => true]);
    }
    $leaver = Student::create(['name' => 'Leaver', 'dob' => '2008-01-01', 'gender' => 'female']);
    GradeStudent::create(['student_id' => $leaver->id, 'grade' => $terminal->id, 'is_current' => true]);

    $component = Livewire::actingAs($admin)
        ->test(PromoteStudents::class)
        ->set('selectedGrades', [$promoting->id, $terminal->id]);

    expect($component->instance()->selectionImpact())->toBe(['promoting' => 3, 'graduating' => 1]);

    $html = $component->html();
    expect($html)->toContain('Promote 3 student(s) and graduate 1 student(s)?');
    expect($html)->toContain('cannot be undone');
});

/* ------------------------------------------------------------ bad route ids */

test('a detail page for a missing record 404s instead of crashing', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // Every detail page should behave the same way; book-detail used to 500
    // because find() returned null into a typed property.
    $this->actingAs($admin)->get('/book-detail/999999')->assertNotFound();
    $this->actingAs($admin)->get('/student-detail/999999')->assertNotFound();
    $this->actingAs($admin)->get('/volunteer-detail/999999')->assertNotFound();
});

test('a real book detail page still loads', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $book = Book::create(['title' => 'A Real Book', 'author' => 'Author', 'publisher' => 'Pub', 'class' => 'A', 'category' => 'Fiction', 'copies' => 1]);

    $this->actingAs($admin)->get("/book-detail/{$book->id}")->assertOk()->assertSee('A Real Book');
});

/* --------------------------------------------------------- double check-out */

test('checking a student out twice does not throw', function () {
    // Two staff members, or the dashboard "check out all" plus a stale row on
    // the attendance page, used to make the second click a 500.
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Leaving Student', 'dob' => '2012-01-01', 'gender' => 'male']);
    $attendance = Attendance::create(['student_id' => $student->id, 'date' => now(), 'current_in' => true, 'total_time' => 0]);
    AttendanceAttr::create(['attendance_id' => $attendance->id, 'student_id' => $student->id, 'date' => now(), 'time_in' => now()->subHour()]);

    Livewire::actingAs($user)->test(AttendanceStudent::class)->call('checkOut', $student->id);
    expect($attendance->fresh()->current_in)->toBeFalsy();

    // The second call is the regression.
    Livewire::actingAs($user)->test(AttendanceStudent::class)->call('checkOut', $student->id)->assertOk();
    Livewire::actingAs($user)->test(QuickCheckInStudents::class)->call('checkOut', $student->id)->assertOk();
});

test('checking a student out with no attendance row at all does not throw', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Never Arrived', 'dob' => '2012-01-01', 'gender' => 'male']);

    Livewire::actingAs($user)->test(AttendanceStudent::class)->call('checkOut', $student->id)->assertOk();
    Livewire::actingAs($user)->test(QuickCheckInStudents::class)->call('checkOut', $student->id)->assertOk();
});

test('checking a volunteer out twice does not throw', function () {
    $user = User::factory()->create();
    $volunteer = Volunteer::create(['name' => 'Leaving Volunteer', 'status' => 'active']);
    $attendance = VolunteerAttendance::create(['volunteer_id' => $volunteer->id, 'date' => now(), 'current_in' => true, 'total_time' => 0]);
    VolunteerAttendanceAttr::create(['volunteer_attendance_id' => $attendance->id, 'volunteer_id' => $volunteer->id, 'date' => now(), 'time_in' => now()->subHour()]);

    Livewire::actingAs($user)->test(AttendanceVolunteer::class)->call('checkOut', $volunteer->id);
    expect($attendance->fresh()->current_in)->toBeFalsy();

    Livewire::actingAs($user)->test(AttendanceVolunteer::class)->call('checkOut', $volunteer->id)->assertOk();
    Livewire::actingAs($user)->test(QuickCheckInVolunteers::class)->call('checkOut', $volunteer->id)->assertOk();
});

/* ------------------------------------------------------- collapsed sidebar */

test('the collapsed sidebar still offers a way into Books, Reports and Admin', function () {
    // Collapsing used to hide the whole <li> for each group, and the state
    // persists in localStorage -- so one click permanently removed every
    // report, the book module and all admin pages from the nav.
    $admin = User::factory()->create(['role' => 'admin']);

    $html = $this->actingAs($admin)->get('/dashboard')->getContent();

    foreach ([
        ['/books', 'Books'],
        [route('reports.enrollment'), 'Reports'],
        ['/users', 'Admin'],
    ] as [$href, $label]) {
        // A collapsed-only icon link, shown precisely when the group is hidden.
        expect($html)->toMatch('/<li class="hidden" :class="\{ \'lg:block\': collapsed \}">\s*<a href="'.preg_quote($href, '/').'"[^>]*title="'.$label.'"/');
    }
});
