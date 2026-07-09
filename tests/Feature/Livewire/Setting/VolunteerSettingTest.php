<?php

use App\Livewire\Setting\Volunteer as VolunteerComponent;
use App\Livewire\Setting\VolunteerList;
use App\Models\User;
use App\Models\Volunteer;
use Livewire\Livewire;

test('creating a volunteer persists it', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(VolunteerComponent::class)
        ->set('name', 'Jane Volunteer')
        ->set('phone', '555-1234')
        ->set('email', 'jane@example.com')
        ->call('saveVolunteer')
        ->assertDispatched('volunteer-changed');

    $volunteer = Volunteer::where('name', 'Jane Volunteer')->first();
    expect($volunteer)->not->toBeNull();
    expect($volunteer->phone)->toBe('555-1234');
    expect($volunteer->status)->toBe(App\Enums\VolunteerStatus::Active);
});

test('creating a volunteer requires a name', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(VolunteerComponent::class)
        ->set('name', '')
        ->call('saveVolunteer')
        ->assertHasErrors(['name']);
});

test('editing a volunteer updates its fields', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $volunteer = Volunteer::create(['name' => 'Original Name', 'status' => 'active']);

    Livewire::actingAs($admin)
        ->test(VolunteerComponent::class, ['volunteerId' => $volunteer->id])
        ->set('name', 'Updated Name')
        ->set('notes', 'Usually comes Tuesdays')
        ->call('saveVolunteer')
        ->assertDispatched('volunteer-changed');

    $volunteer->refresh();
    expect($volunteer->name)->toBe('Updated Name');
    expect($volunteer->notes)->toBe('Usually comes Tuesdays');
});

test('editing a volunteer status to inactive deactivates it without deleting it', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $volunteer = Volunteer::create(['name' => 'Active Volunteer', 'status' => 'active']);

    Livewire::actingAs($admin)
        ->test(VolunteerComponent::class, ['volunteerId' => $volunteer->id])
        ->set('status', 'inactive')
        ->call('saveVolunteer');

    $volunteer->refresh();
    expect($volunteer)->not->toBeNull();
    expect($volunteer->isActive())->toBeFalse();
});

test('VolunteerList shows both active and inactive volunteers', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Volunteer::create(['name' => 'Active One', 'status' => 'active']);
    Volunteer::create(['name' => 'Inactive One', 'status' => 'inactive']);

    Livewire::actingAs($admin)
        ->test(VolunteerList::class)
        ->assertSee('Active One')
        ->assertSee('Inactive One');
});
