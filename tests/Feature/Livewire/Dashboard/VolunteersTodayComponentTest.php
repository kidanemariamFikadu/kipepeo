<?php

use App\Livewire\Dashboard\VolunteersTodayComponent;
use App\Models\User;
use App\Models\Volunteer;
use App\Models\VolunteerAttendance;
use Livewire\Livewire;

test('volunteers today component counts distinct volunteers who came today and how many are currently in', function () {
    $user = User::factory()->create();
    $a = Volunteer::create(['name' => 'Volunteer A', 'status' => 'active']);
    $b = Volunteer::create(['name' => 'Volunteer B', 'status' => 'active']);
    $c = Volunteer::create(['name' => 'Volunteer C', 'status' => 'active']);

    VolunteerAttendance::create(['volunteer_id' => $a->id, 'date' => now(), 'current_in' => true]);
    VolunteerAttendance::create(['volunteer_id' => $b->id, 'date' => now(), 'current_in' => false]);
    VolunteerAttendance::create(['volunteer_id' => $c->id, 'date' => now()->subDay(), 'current_in' => true]);

    $component = Livewire::actingAs($user)->test(VolunteersTodayComponent::class);

    expect($component->viewData('volunteersToday'))->toBe(2);
    expect($component->viewData('volunteersCurrentlyIn'))->toBe(1);
});

test('total hours today includes stored time plus live time for volunteers still checked in', function () {
    $user = User::factory()->create();
    $a = Volunteer::create(['name' => 'Volunteer A', 'status' => 'active']);
    $b = Volunteer::create(['name' => 'Volunteer B', 'status' => 'active']);

    $checkedOut = VolunteerAttendance::create(['volunteer_id' => $a->id, 'date' => now(), 'current_in' => false, 'total_time' => 3600]);

    $stillIn = VolunteerAttendance::create(['volunteer_id' => $b->id, 'date' => now(), 'current_in' => true, 'total_time' => 0]);
    $stillIn->attrs()->create(['volunteer_id' => $b->id, 'date' => now(), 'time_in' => now()->subHour()->format('H:i:s')]);

    $component = Livewire::actingAs($user)->test(VolunteersTodayComponent::class);

    expect($component->viewData('totalHoursToday'))->toBe(2.0);
});

test('the component refreshes its counts when a dashboard-changed event fires', function () {
    $user = User::factory()->create();
    $component = Livewire::actingAs($user)->test(VolunteersTodayComponent::class);
    expect($component->viewData('volunteersToday'))->toBe(0);

    $volunteer = Volunteer::create(['name' => 'Volunteer A', 'status' => 'active']);
    VolunteerAttendance::create(['volunteer_id' => $volunteer->id, 'date' => now(), 'current_in' => true]);

    $component->dispatch('dashboard-changed');

    expect($component->viewData('volunteersToday'))->toBe(1);
});
