<?php

use App\Livewire\Attendance\QuickCheckInVolunteers;
use App\Models\User;
use App\Models\Volunteer;
use App\Models\VolunteerAttendance;
use App\Models\VolunteerAttendanceAttr;
use Livewire\Livewire;

test('checkIn creates a volunteer attendance record and an open attendance attribute row', function () {
    $user = User::factory()->create();
    $volunteer = Volunteer::create(['name' => 'Test Volunteer', 'status' => 'active']);

    Livewire::actingAs($user)
        ->test(QuickCheckInVolunteers::class)
        ->call('checkIn', $volunteer->id)
        ->assertDispatched('dashboard-changed');

    $attendance = VolunteerAttendance::where('volunteer_id', $volunteer->id)->first();
    expect($attendance)->not->toBeNull();
    expect($attendance->current_in)->toBeTruthy();
    expect(VolunteerAttendanceAttr::where('volunteer_attendance_id', $attendance->id)->whereNull('time_out')->exists())->toBeTrue();
});

test('checkOut closes the open attendance attribute and accumulates total_time', function () {
    $user = User::factory()->create();
    $volunteer = Volunteer::create(['name' => 'Test Volunteer', 'status' => 'active']);

    Livewire::actingAs($user)->test(QuickCheckInVolunteers::class)->call('checkIn', $volunteer->id);
    Livewire::actingAs($user)->test(QuickCheckInVolunteers::class)
        ->call('checkOut', $volunteer->id)
        ->assertDispatched('dashboard-changed');

    $attendance = VolunteerAttendance::where('volunteer_id', $volunteer->id)->first();
    expect($attendance->current_in)->toBeFalsy();
    expect(VolunteerAttendanceAttr::where('volunteer_attendance_id', $attendance->id)->whereNotNull('time_out')->exists())->toBeTrue();
});

test('search filters the results list by name', function () {
    $user = User::factory()->create();
    Volunteer::create(['name' => 'Aisha Tadesse', 'status' => 'active']);
    Volunteer::create(['name' => 'Biruk Mekonnen', 'status' => 'active']);

    $component = Livewire::actingAs($user)->test(QuickCheckInVolunteers::class)->set('search', 'Aisha');

    $names = $component->get('results')->pluck('name');
    expect($names)->toContain('Aisha Tadesse');
    expect($names)->not->toContain('Biruk Mekonnen');
});
