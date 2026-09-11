<?php

use App\Livewire\Report\AttendanceReport;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

test('filter aggregates attendance totals within the date range', function () {
    $user = User::factory()->create();
    $studentA = Student::create(['name' => 'A', 'dob' => '2010-01-01', 'gender' => 'male']);
    $studentB = Student::create(['name' => 'B', 'dob' => '2010-01-01', 'gender' => 'female']);

    Attendance::create(['student_id' => $studentA->id, 'date' => now(), 'current_in' => false, 'total_time' => 3600]);
    Attendance::create(['student_id' => $studentB->id, 'date' => now(), 'current_in' => false, 'total_time' => 7200]);
    Attendance::create(['student_id' => $studentA->id, 'date' => now()->subDays(10), 'current_in' => false, 'total_time' => 9999]);

    $component = Livewire::actingAs($user)
        ->test(AttendanceReport::class)
        ->set('fromDate', now()->format('Y-m-d'))
        ->set('toDate', now()->format('Y-m-d'))
        ->call('filter');

    expect($component->get('totalStudents'))->toBe(2);
    expect((int) $component->get('averageAttendanceDuration'))->toBe(5400);
});

test('mounting the component auto-loads data for the default date range', function () {
    // Regression test: the report used to render blank on first page load because
    // mount() only set the default dates without ever calling filter().
    $user = User::factory()->create();
    $student = Student::create(['name' => 'A', 'dob' => '2010-01-01', 'gender' => 'male']);
    Attendance::create(['student_id' => $student->id, 'date' => now(), 'current_in' => false, 'total_time' => 1800]);

    $component = Livewire::actingAs($user)->test(AttendanceReport::class);

    expect($component->get('totalStudents'))->toBe(1);
});

test('an empty date range does not crash the average duration calculation', function () {
    // Regression test: Collection::avg() on an empty collection returns null, and the
    // old secondsToHms() passed that straight into floor(), which errors on PHP 8.1+.
    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(AttendanceReport::class)
        ->set('fromDate', now()->subYears(5)->format('Y-m-d'))
        ->set('toDate', now()->subYears(5)->format('Y-m-d'))
        ->call('filter');

    expect($component->get('totalStudents'))->toBe(0);
    expect($component->instance()->secondsToHms(null))->toBe('00:00:00');
});

test('filter requires the to-date to be on or after the from-date', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(AttendanceReport::class)
        ->set('fromDate', now()->format('Y-m-d'))
        ->set('toDate', now()->subDay()->format('Y-m-d'))
        ->call('filter')
        ->assertHasErrors(['toDate']);
});

test('hoursByStudent totals days present and time for each student in range', function () {
    $user = User::factory()->create();
    $studentA = Student::create(['name' => 'A', 'dob' => '2010-01-01', 'gender' => 'male']);
    $studentB = Student::create(['name' => 'B', 'dob' => '2010-01-01', 'gender' => 'female']);

    Attendance::create(['student_id' => $studentA->id, 'date' => now(), 'current_in' => false, 'total_time' => 3600]);
    Attendance::create(['student_id' => $studentA->id, 'date' => now()->subDay(), 'current_in' => false, 'total_time' => 1800]);
    Attendance::create(['student_id' => $studentB->id, 'date' => now(), 'current_in' => false, 'total_time' => 7200]);

    $component = Livewire::actingAs($user)
        ->test(AttendanceReport::class)
        ->set('fromDate', now()->subDays(5)->format('Y-m-d'))
        ->set('toDate', now()->format('Y-m-d'))
        ->call('filter');

    $hoursByStudent = $component->viewData('hoursByStudent');

    $rowA = $hoursByStudent->firstWhere(fn ($row) => $row['student']->id === $studentA->id);
    $rowB = $hoursByStudent->firstWhere(fn ($row) => $row['student']->id === $studentB->id);

    expect($rowA['visits'])->toBe(2);
    expect($rowA['totalSeconds'])->toBe(5400);
    expect($rowB['visits'])->toBe(1);
    expect($rowB['totalSeconds'])->toBe(7200);
});

test('the Hours by Student table shows each student\'s gender', function () {
    $user = User::factory()->create();
    $studentA = Student::create(['name' => 'Male Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    $studentB = Student::create(['name' => 'Female Student', 'dob' => '2010-01-01', 'gender' => 'FEMALE']);

    Attendance::create(['student_id' => $studentA->id, 'date' => now(), 'current_in' => false, 'total_time' => 3600]);
    Attendance::create(['student_id' => $studentB->id, 'date' => now(), 'current_in' => false, 'total_time' => 3600]);

    $html = Livewire::actingAs($user)
        ->test(AttendanceReport::class)
        ->set('fromDate', now()->format('Y-m-d'))
        ->set('toDate', now()->format('Y-m-d'))
        ->call('filter')
        ->html();

    expect($html)->toContain('<th class="px-4 py-3">Gender</th>');
    expect($html)->toContain('Male');
    // Stored gender casing is normalized for display, same as the other
    // gender breakdowns on this page.
    expect($html)->toContain('Female');
    expect($html)->not->toContain('FEMALE');
});

test('selecting a student scopes every card and chart to that student, not the whole cohort', function () {
    // Regression test: studentId used to only filter the attendanceLog table
    // at the bottom of the page - every summary card and chart above it kept
    // showing the full cohort's numbers regardless of which student was
    // selected.
    $user = User::factory()->create();
    $studentA = Student::create(['name' => 'A', 'dob' => '2010-01-01', 'gender' => 'male']);
    $studentB = Student::create(['name' => 'B', 'dob' => '2010-01-01', 'gender' => 'female']);

    Attendance::create(['student_id' => $studentA->id, 'date' => now(), 'current_in' => false, 'total_time' => 3600]);
    Attendance::create(['student_id' => $studentB->id, 'date' => now(), 'current_in' => false, 'total_time' => 7200]);

    $component = Livewire::actingAs($user)
        ->test(AttendanceReport::class)
        ->set('fromDate', now()->format('Y-m-d'))
        ->set('toDate', now()->format('Y-m-d'))
        ->set('studentId', $studentA->id)
        ->call('filter');

    expect($component->get('totalStudents'))->toBe(1);
    expect((int) $component->get('averageAttendanceDuration'))->toBe(3600);

    $hoursByStudent = $component->viewData('hoursByStudent');
    expect($hoursByStudent)->toHaveCount(1);
    expect($hoursByStudent->first()['student']->id)->toBe($studentA->id);

    // Student B's data must not leak into a report scoped to student A.
    expect($component->viewData('girlsAttendance'))->toHaveCount(0);
});

test('attendanceLog is only populated once a studentId filter is set', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'A', 'dob' => '2010-01-01', 'gender' => 'male']);
    Attendance::create(['student_id' => $student->id, 'date' => now(), 'current_in' => false, 'total_time' => 3600]);

    $withoutFilter = Livewire::actingAs($user)
        ->test(AttendanceReport::class)
        ->call('filter');
    expect($withoutFilter->viewData('attendanceLog'))->toHaveCount(0);

    $withFilter = Livewire::actingAs($user)
        ->test(AttendanceReport::class)
        ->set('studentId', $student->id)
        ->call('filter');
    expect($withFilter->viewData('attendanceLog'))->toHaveCount(1);
});

test('the Hours by Student table is paginated on screen but the print table has every row', function () {
    $user = User::factory()->create();

    collect(range(1, 15))->each(function ($i) {
        $student = Student::create(['name' => "Student {$i}", 'dob' => '2010-01-01', 'gender' => 'male']);
        Attendance::create(['student_id' => $student->id, 'date' => now(), 'current_in' => false, 'total_time' => 60]);
    });

    $component = Livewire::actingAs($user)
        ->test(AttendanceReport::class)
        ->set('fromDate', now()->format('Y-m-d'))
        ->set('toDate', now()->format('Y-m-d'))
        ->call('filter');

    // Default perPage is 10.
    expect($component->viewData('hoursByStudentPage'))->toHaveCount(10);
    expect($component->viewData('hoursByStudent'))->toHaveCount(15);

    $component->call('gotoPage', 2, 'hours-student-page');
    expect($component->viewData('hoursByStudentPage'))->toHaveCount(5);
    // The print copy is unaffected by which page is showing on screen.
    expect($component->viewData('hoursByStudent'))->toHaveCount(15);
});

test('changing perPage or re-filtering resets every table back to page 1', function () {
    $user = User::factory()->create();

    collect(range(1, 15))->each(function ($i) {
        $student = Student::create(['name' => "Student {$i}", 'dob' => '2010-01-01', 'gender' => 'male']);
        Attendance::create(['student_id' => $student->id, 'date' => now(), 'current_in' => false, 'total_time' => 60]);
    });

    $component = Livewire::actingAs($user)
        ->test(AttendanceReport::class)
        ->set('fromDate', now()->format('Y-m-d'))
        ->set('toDate', now()->format('Y-m-d'))
        ->call('filter')
        ->call('gotoPage', 2, 'hours-student-page');

    expect($component->viewData('hoursByStudentPage')->currentPage())->toBe(2);

    $component->call('filter');
    expect($component->viewData('hoursByStudentPage')->currentPage())->toBe(1);

    $component->call('gotoPage', 2, 'hours-student-page');
    expect($component->viewData('hoursByStudentPage')->currentPage())->toBe(2);

    $component->set('perPage', 20);
    expect($component->viewData('hoursByStudentPage')->currentPage())->toBe(1);
});

test("a girl's rank for the Top 5 badge survives pagination instead of resetting per page", function () {
    $user = User::factory()->create();

    // 12 girls, all with the same consistency (100%), so ordering is stable
    // and rank 11 should land on page 2 when perPage is 10.
    collect(range(1, 12))->each(function ($i) {
        $student = Student::create(['name' => "Girl {$i}", 'dob' => '2010-01-01', 'gender' => 'female']);
        Attendance::create(['student_id' => $student->id, 'date' => now(), 'current_in' => false, 'total_time' => 60]);
    });

    $component = Livewire::actingAs($user)
        ->test(AttendanceReport::class)
        ->set('fromDate', now()->format('Y-m-d'))
        ->set('toDate', now()->format('Y-m-d'))
        ->call('filter')
        ->call('gotoPage', 2, 'girls-page');

    $page2 = $component->viewData('girlsAttendancePage');
    expect($page2)->toHaveCount(2);
    expect($page2->first()['rank'])->toBe(11);
    // Rank 11 is past the top 5, so it must not be flagged as "Top".
    expect($page2->first()['rank'] <= 5)->toBeFalse();
});
