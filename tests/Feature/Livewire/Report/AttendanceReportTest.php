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
