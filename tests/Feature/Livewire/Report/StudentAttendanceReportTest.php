<?php

use App\Livewire\Report\StudentAttendance;
use App\Models\Attendance;
use App\Models\AttendanceAttr;
use App\Models\School;
use App\Models\SchoolStudent;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\User;
use Livewire\Livewire;

test('getStudentByDate returns a formatted list of students attending on the given date', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Attending Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    $school = School::create(['name' => 'Test School']);
    SchoolStudent::create(['student_id' => $student->id, 'school_id' => $school->id, 'is_current' => true]);
    StudentGuardian::create(['student_id' => $student->id, 'guardian_name' => 'Guardian', 'guardian_phone' => '123', 'is_primary' => true]);

    $attendance = Attendance::create(['student_id' => $student->id, 'date' => '2026-01-15', 'current_in' => false, 'total_time' => 3600]);
    AttendanceAttr::create(['attendance_id' => $attendance->id, 'student_id' => $student->id, 'date' => '2026-01-15', 'time_in' => '08:00', 'time_out' => '09:00']);

    $component = Livewire::actingAs($user)
        ->test(StudentAttendance::class)
        ->set('date', '2026-01-15')
        ->call('getStudentByDate');

    $students = $component->get('students');
    expect($students)->toHaveCount(1);
    expect($students->first()['name'])->toBe('Attending Student');
    expect($students->first()['total_time'])->toBe('01:00:00');
    expect($students->first()['school'])->toBe('Test School');
});

test('getStudentByDate reports the current school, not an old one', function () {
    // Regression test: `school` used to read the SchoolStudent pivot row's `name`
    // attribute (which doesn't exist - name lives on the related School), so this
    // column always fell back to "N/A" regardless of data. It also picked whichever
    // school row came first rather than the one flagged is_current.
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Transferred Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    $oldSchool = School::create(['name' => 'Old School']);
    $newSchool = School::create(['name' => 'New School']);
    SchoolStudent::create(['student_id' => $student->id, 'school_id' => $oldSchool->id, 'is_current' => false]);
    SchoolStudent::create(['student_id' => $student->id, 'school_id' => $newSchool->id, 'is_current' => true]);

    $attendance = Attendance::create(['student_id' => $student->id, 'date' => '2026-01-15', 'current_in' => false, 'total_time' => 600]);
    AttendanceAttr::create(['attendance_id' => $attendance->id, 'student_id' => $student->id, 'date' => '2026-01-15', 'time_in' => '08:00', 'time_out' => '08:10']);

    $component = Livewire::actingAs($user)
        ->test(StudentAttendance::class)
        ->set('date', '2026-01-15')
        ->call('getStudentByDate');

    expect($component->get('students')->first()['school'])->toBe('New School');
});

test('getStudentByDate requires a date', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(StudentAttendance::class)
        ->set('date', '')
        ->call('getStudentByDate')
        ->assertHasErrors(['date']);
});

test('mounting the component defaults to today and auto-loads attendance', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Today Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    $attendance = Attendance::create(['student_id' => $student->id, 'date' => now(), 'current_in' => true, 'total_time' => 600]);
    AttendanceAttr::create(['attendance_id' => $attendance->id, 'student_id' => $student->id, 'date' => now(), 'time_in' => '08:00']);

    $component = Livewire::actingAs($user)->test(StudentAttendance::class);

    expect($component->get('date'))->toBe(now()->format('Y-m-d'));
    expect($component->get('students'))->toHaveCount(1);
});

test('the roster table numbers each row', function () {
    $user = User::factory()->create();
    $a = Student::create(['name' => 'A Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    $b = Student::create(['name' => 'B Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    Attendance::create(['student_id' => $a->id, 'date' => '2026-01-15', 'current_in' => true, 'total_time' => 0]);
    Attendance::create(['student_id' => $b->id, 'date' => '2026-01-15', 'current_in' => true, 'total_time' => 0]);

    $html = Livewire::actingAs($user)
        ->test(StudentAttendance::class)
        ->set('date', '2026-01-15')
        ->call('getStudentByDate')
        ->html();

    expect($html)->toContain('<th class="px-4 py-3">#</th>');
});
