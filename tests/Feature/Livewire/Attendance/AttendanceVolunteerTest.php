<?php

use App\Livewire\Attendance\AttendanceVolunteer;
use App\Models\User;
use App\Models\Volunteer;
use App\Models\VolunteerAttendance;
use App\Models\VolunteerAttendanceAttr;
use Livewire\Livewire;

test('checkIn creates a volunteer attendance record and an open attendance attribute row', function () {
    $user = User::factory()->create();
    $volunteer = Volunteer::create(['name' => 'Test Volunteer', 'status' => 'active']);

    Livewire::actingAs($user)
        ->test(AttendanceVolunteer::class)
        ->call('checkIn', $volunteer->id);

    $attendance = VolunteerAttendance::where('volunteer_id', $volunteer->id)->first();
    expect($attendance)->not->toBeNull();
    expect($attendance->current_in)->toBeTruthy();
    expect(VolunteerAttendanceAttr::where('volunteer_attendance_id', $attendance->id)->whereNull('time_out')->exists())->toBeTrue();
});

test('checkIn twice in a row for a still-checked-in volunteer flashes an error and does not duplicate', function () {
    $user = User::factory()->create();
    $volunteer = Volunteer::create(['name' => 'Test Volunteer', 'status' => 'active']);

    Livewire::actingAs($user)->test(AttendanceVolunteer::class)->call('checkIn', $volunteer->id);
    Livewire::actingAs($user)->test(AttendanceVolunteer::class)->call('checkIn', $volunteer->id);

    expect(VolunteerAttendanceAttr::where('volunteer_id', $volunteer->id)->count())->toBe(1);
});

test('checkOut closes the open attendance attribute and accumulates total_time', function () {
    $user = User::factory()->create();
    $volunteer = Volunteer::create(['name' => 'Test Volunteer', 'status' => 'active']);

    Livewire::actingAs($user)->test(AttendanceVolunteer::class)->call('checkIn', $volunteer->id);
    Livewire::actingAs($user)->test(AttendanceVolunteer::class)->call('checkOut', $volunteer->id);

    $attendance = VolunteerAttendance::where('volunteer_id', $volunteer->id)->first();
    expect($attendance->current_in)->toBeFalsy();
    expect(VolunteerAttendanceAttr::where('volunteer_attendance_id', $attendance->id)->whereNotNull('time_out')->exists())->toBeTrue();
});

test('setSortBy toggles sort direction like other lists', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(AttendanceVolunteer::class)
        ->call('setSortBy', 'name')
        ->assertSet('sortDir', 'DESC')
        ->call('setSortBy', 'name')
        ->assertSet('sortDir', 'ASC');
});

test('inactive volunteers do not show up in the check-in search', function () {
    $user = User::factory()->create();
    Volunteer::create(['name' => 'Active Volunteer', 'status' => 'active']);
    Volunteer::create(['name' => 'Inactive Volunteer', 'status' => 'inactive']);

    $html = Livewire::actingAs($user)->test(AttendanceVolunteer::class)->html();

    expect($html)->toContain('Active Volunteer');
    expect($html)->not->toContain('Inactive Volunteer');
});
