<?php

use App\Livewire\DataEntry\AddStudentAttendance;
use App\Models\Attendance;
use App\Models\AttendanceAttr;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

test('addAttendance records a new attendance with the computed total time', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Test', 'dob' => '2010-01-01', 'gender' => 'male']);

    Livewire::actingAs($user)
        ->test(AddStudentAttendance::class)
        ->set('form.student_id', $student->id)
        ->set('form.date', now()->format('Y-m-d'))
        ->set('form.startTime', '08:00')
        ->set('form.endTime', '09:30')
        ->call('addAttendance')
        ->assertDispatched('student-changed');

    $attendance = Attendance::where('student_id', $student->id)->first();
    expect($attendance)->not->toBeNull();
    expect((int) $attendance->total_time)->toBe(90 * 60);
    expect(AttendanceAttr::where('attendance_id', $attendance->id)->count())->toBe(1);
});

test('addAttendance requires end time to be after start time', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Test', 'dob' => '2010-01-01', 'gender' => 'male']);

    Livewire::actingAs($user)
        ->test(AddStudentAttendance::class)
        ->set('form.student_id', $student->id)
        ->set('form.date', now()->format('Y-m-d'))
        ->set('form.startTime', '09:30')
        ->set('form.endTime', '08:00')
        ->call('addAttendance')
        ->assertHasErrors(['form.endTime']);
});
