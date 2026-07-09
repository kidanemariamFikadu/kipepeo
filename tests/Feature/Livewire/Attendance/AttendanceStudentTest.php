<?php

use App\Livewire\Attendance\AttendanceStudent;
use App\Models\Attendance;
use App\Models\AttendanceAttr;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

test('checkIn creates an attendance record and an open attendance attribute row', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Test', 'dob' => '2010-01-01', 'gender' => 'male']);

    Livewire::actingAs($user)
        ->test(AttendanceStudent::class)
        ->call('checkIn', $student->id);

    $attendance = Attendance::where('student_id', $student->id)->first();
    expect($attendance)->not->toBeNull();
    expect($attendance->current_in)->toBeTruthy();
    expect(AttendanceAttr::where('attendance_id', $attendance->id)->whereNull('time_out')->exists())->toBeTrue();
});

test('checkIn twice in a row for a still-checked-in student flashes an error and does not duplicate', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Test', 'dob' => '2010-01-01', 'gender' => 'male']);

    Livewire::actingAs($user)->test(AttendanceStudent::class)->call('checkIn', $student->id);
    Livewire::actingAs($user)->test(AttendanceStudent::class)->call('checkIn', $student->id);

    expect(AttendanceAttr::where('student_id', $student->id)->count())->toBe(1);
});

test('checkOut closes the open attendance attribute and accumulates total_time', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Test', 'dob' => '2010-01-01', 'gender' => 'male']);

    Livewire::actingAs($user)->test(AttendanceStudent::class)->call('checkIn', $student->id);
    Livewire::actingAs($user)->test(AttendanceStudent::class)->call('checkOut', $student->id);

    $attendance = Attendance::where('student_id', $student->id)->first();
    expect($attendance->current_in)->toBeFalsy();
    expect(AttendanceAttr::where('attendance_id', $attendance->id)->whereNotNull('time_out')->exists())->toBeTrue();
});

test('setSortBy toggles sort direction like other lists', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(AttendanceStudent::class)
        ->call('setSortBy', 'name')
        ->assertSet('sortDir', 'DESC')
        ->call('setSortBy', 'name')
        ->assertSet('sortDir', 'ASC');
});
