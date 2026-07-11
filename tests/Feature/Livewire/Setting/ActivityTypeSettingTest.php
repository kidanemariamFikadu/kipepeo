<?php

use App\Livewire\Setting\ActivityType as ActivityTypeComponent;
use App\Livewire\Setting\ActivityTypeList;
use App\Models\ActivityType;
use App\Models\User;
use App\Models\Volunteer;
use App\Models\VolunteerActivity;
use App\Models\VolunteerAttendance;
use Livewire\Livewire;

test('creating an activity type persists it', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(ActivityTypeComponent::class)
        ->set('name', 'Chess Club')
        ->call('saveActivityType')
        ->assertDispatched('activity-type-changed');

    $activityType = ActivityType::where('name', 'Chess Club')->first();
    expect($activityType)->not->toBeNull();
});

test('creating a duplicate activity type name fails the unique validation rule', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    ActivityType::create(['name' => 'Tutoring']);

    Livewire::actingAs($admin)
        ->test(ActivityTypeComponent::class)
        ->set('name', 'Tutoring')
        ->call('saveActivityType')
        ->assertHasErrors(['name']);

    expect(ActivityType::where('name', 'Tutoring')->count())->toBe(1);
});

test('editing an activity type without changing its name succeeds', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $activityType = ActivityType::create(['name' => 'Tutoring']);

    Livewire::actingAs($admin)
        ->test(ActivityTypeComponent::class, ['activityTypeId' => $activityType->id])
        ->set('name', 'Tutoring')
        ->call('saveActivityType')
        ->assertHasNoErrors();

    expect(ActivityType::where('name', 'Tutoring')->count())->toBe(1);
});

test('removeActivityType refuses to delete an activity type with associated activities', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $activityType = ActivityType::create(['name' => 'Populated Type']);
    $volunteer = Volunteer::create(['name' => 'A Volunteer', 'status' => 'active']);
    $attendance = VolunteerAttendance::create(['volunteer_id' => $volunteer->id, 'date' => now(), 'current_in' => true]);
    VolunteerActivity::create([
        'volunteer_attendance_id' => $attendance->id,
        'volunteer_id' => $volunteer->id,
        'activity_type_id' => $activityType->id,
        'date' => now(),
    ]);

    Livewire::actingAs($admin)
        ->test(ActivityTypeList::class)
        ->call('removeActivityType', $activityType->id)
        ->assertDispatched('MessageChanged');

    expect(ActivityType::find($activityType->id))->not->toBeNull();
});

test('removeActivityType deletes an activity type with no associated activities', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $activityType = ActivityType::create(['name' => 'Unused Type']);

    Livewire::actingAs($admin)
        ->test(ActivityTypeList::class)
        ->call('removeActivityType', $activityType->id);

    expect(ActivityType::find($activityType->id))->toBeNull();
});
