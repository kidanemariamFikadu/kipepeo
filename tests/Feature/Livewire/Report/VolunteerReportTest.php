<?php

use App\Livewire\Report\VolunteerReport;
use App\Models\ActivityType;
use App\Models\User;
use App\Models\Volunteer;
use App\Models\VolunteerActivity;
use App\Models\VolunteerAttendance;
use Livewire\Livewire;

test('volunteer report sums hours per volunteer from total_time, not per-activity', function () {
    $user = User::factory()->create();
    $volunteer = Volunteer::create(['name' => 'Test Volunteer', 'status' => 'active']);
    $activityType = ActivityType::create(['name' => 'Tutoring']);
    $attendance = VolunteerAttendance::create(['volunteer_id' => $volunteer->id, 'date' => now(), 'current_in' => false, 'total_time' => 3600]);

    // Two activities logged during the same single visit -- hours must not double-count per activity.
    VolunteerActivity::create(['volunteer_attendance_id' => $attendance->id, 'volunteer_id' => $volunteer->id, 'activity_type_id' => $activityType->id, 'date' => now()]);
    VolunteerActivity::create(['volunteer_attendance_id' => $attendance->id, 'volunteer_id' => $volunteer->id, 'activity_type_id' => $activityType->id, 'date' => now()]);

    $component = Livewire::actingAs($user)->test(VolunteerReport::class);

    $hoursByVolunteer = $component->viewData('hoursByVolunteer');
    expect($hoursByVolunteer->first()['totalSeconds'])->toBe(3600);
    expect($hoursByVolunteer->first()['visits'])->toBe(1);
    expect($component->viewData('totalActivities'))->toBe(2);
});

test('volunteer report counts activities grouped by activity type', function () {
    $user = User::factory()->create();
    $volunteer = Volunteer::create(['name' => 'Test Volunteer', 'status' => 'active']);
    $tutoring = ActivityType::create(['name' => 'Tutoring']);
    $mentorship = ActivityType::create(['name' => 'Mentorship']);
    $attendance = VolunteerAttendance::create(['volunteer_id' => $volunteer->id, 'date' => now(), 'current_in' => false]);

    VolunteerActivity::create(['volunteer_attendance_id' => $attendance->id, 'volunteer_id' => $volunteer->id, 'activity_type_id' => $tutoring->id, 'date' => now()]);
    VolunteerActivity::create(['volunteer_attendance_id' => $attendance->id, 'volunteer_id' => $volunteer->id, 'activity_type_id' => $tutoring->id, 'date' => now()]);
    VolunteerActivity::create(['volunteer_attendance_id' => $attendance->id, 'volunteer_id' => $volunteer->id, 'activity_type_id' => $mentorship->id, 'date' => now()]);

    $counts = Livewire::actingAs($user)->test(VolunteerReport::class)->viewData('activityCountsByType');

    $tutoringCount = $counts->firstWhere(fn ($row) => $row['activityType']->id === $tutoring->id);
    expect($tutoringCount['count'])->toBe(2);
});

test('volunteer report filters by date range', function () {
    $user = User::factory()->create();
    $volunteer = Volunteer::create(['name' => 'Test Volunteer', 'status' => 'active']);
    VolunteerAttendance::create(['volunteer_id' => $volunteer->id, 'date' => now(), 'current_in' => false, 'total_time' => 1800]);
    VolunteerAttendance::create(['volunteer_id' => $volunteer->id, 'date' => now()->subYear(), 'current_in' => false, 'total_time' => 900]);

    $component = Livewire::actingAs($user)->test(VolunteerReport::class)
        ->set('fromDate', now()->subDay()->format('Y-m-d'))
        ->set('toDate', now()->format('Y-m-d'))
        ->call('filter');

    expect($component->viewData('totalHoursSeconds'))->toBe(1800);
});

test('volunteer report shows a per-activity log only when a specific volunteer is selected', function () {
    $user = User::factory()->create();
    $volunteer = Volunteer::create(['name' => 'Test Volunteer', 'status' => 'active']);
    $activityType = ActivityType::create(['name' => 'Tutoring']);
    $attendance = VolunteerAttendance::create(['volunteer_id' => $volunteer->id, 'date' => now(), 'current_in' => false]);
    VolunteerActivity::create(['volunteer_attendance_id' => $attendance->id, 'volunteer_id' => $volunteer->id, 'activity_type_id' => $activityType->id, 'date' => now()]);

    $withoutFilter = Livewire::actingAs($user)->test(VolunteerReport::class);
    expect($withoutFilter->viewData('activityLog'))->toHaveCount(0);

    $withFilter = Livewire::actingAs($user)->test(VolunteerReport::class)
        ->set('volunteerId', $volunteer->id)
        ->call('filter');
    expect($withFilter->viewData('activityLog'))->toHaveCount(1);
});

test('volunteer report computes an estimated stipend as hours times hourly rate', function () {
    $user = User::factory()->create();
    $rated = Volunteer::create(['name' => 'Rated Volunteer', 'status' => 'active', 'hourly_rate' => 100]);
    $unrated = Volunteer::create(['name' => 'Unrated Volunteer', 'status' => 'active']);
    VolunteerAttendance::create(['volunteer_id' => $rated->id, 'date' => now(), 'current_in' => false, 'total_time' => 7200]);
    VolunteerAttendance::create(['volunteer_id' => $unrated->id, 'date' => now(), 'current_in' => false, 'total_time' => 3600]);

    $hoursByVolunteer = Livewire::actingAs($user)->test(VolunteerReport::class)->viewData('hoursByVolunteer');

    $ratedRow = $hoursByVolunteer->firstWhere(fn ($row) => $row['volunteer']->id === $rated->id);
    $unratedRow = $hoursByVolunteer->firstWhere(fn ($row) => $row['volunteer']->id === $unrated->id);

    expect($ratedRow['estStipend'])->toBe(200.0);
    expect($unratedRow['estStipend'])->toBeNull();
});

test('volunteer report validates toDate is not before fromDate', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(VolunteerReport::class)
        ->set('fromDate', now()->format('Y-m-d'))
        ->set('toDate', now()->subDay()->format('Y-m-d'))
        ->call('filter')
        ->assertHasErrors(['toDate']);
});
