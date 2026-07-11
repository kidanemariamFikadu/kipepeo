<?php

use App\Livewire\Attendance\QuickCheckInStudents;
use App\Models\Attendance;
use App\Models\AttendanceAttr;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

test('checkIn creates an attendance record and an open attendance attribute row', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Test', 'dob' => '2010-01-01', 'gender' => 'male']);

    Livewire::actingAs($user)
        ->test(QuickCheckInStudents::class)
        ->call('checkIn', $student->id)
        ->assertDispatched('dashboard-changed');

    $attendance = Attendance::where('student_id', $student->id)->first();
    expect($attendance)->not->toBeNull();
    expect($attendance->current_in)->toBeTruthy();
    expect(AttendanceAttr::where('attendance_id', $attendance->id)->whereNull('time_out')->exists())->toBeTrue();
});

test('checkOut closes the open attendance attribute and accumulates total_time', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Test', 'dob' => '2010-01-01', 'gender' => 'male']);

    Livewire::actingAs($user)->test(QuickCheckInStudents::class)->call('checkIn', $student->id);
    Livewire::actingAs($user)->test(QuickCheckInStudents::class)
        ->call('checkOut', $student->id)
        ->assertDispatched('dashboard-changed');

    $attendance = Attendance::where('student_id', $student->id)->first();
    expect($attendance->current_in)->toBeFalsy();
    expect(AttendanceAttr::where('attendance_id', $attendance->id)->whereNotNull('time_out')->exists())->toBeTrue();
});

test('search filters the results list by name', function () {
    $user = User::factory()->create();
    Student::create(['name' => 'Amara Tesfaye', 'dob' => '2010-01-01', 'gender' => 'female']);
    Student::create(['name' => 'Biruk Alemu', 'dob' => '2011-01-01', 'gender' => 'male']);

    $component = Livewire::actingAs($user)->test(QuickCheckInStudents::class)->set('search', 'Amara');

    $names = $component->get('results')->pluck('name');
    expect($names)->toContain('Amara Tesfaye');
    expect($names)->not->toContain('Biruk Alemu');
});
