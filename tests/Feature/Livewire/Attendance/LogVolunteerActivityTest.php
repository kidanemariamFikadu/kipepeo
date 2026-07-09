<?php

use App\Livewire\Attendance\LogVolunteerActivity;
use App\Models\ActivityType;
use App\Models\Student;
use App\Models\User;
use App\Models\Volunteer;
use App\Models\VolunteerActivity;
use App\Models\VolunteerAttendance;
use Livewire\Livewire;

function checkedInVolunteer(): Volunteer
{
    $volunteer = Volunteer::create(['name' => 'Test Volunteer', 'status' => 'active']);
    VolunteerAttendance::create(['volunteer_id' => $volunteer->id, 'date' => now(), 'current_in' => true]);

    return $volunteer;
}

test('logActivity creates a volunteer activity tied to the currently open attendance visit', function () {
    $user = User::factory()->create();
    $volunteer = checkedInVolunteer();
    $activityType = ActivityType::create(['name' => 'Tutoring']);
    $attendance = VolunteerAttendance::where('volunteer_id', $volunteer->id)->first();

    Livewire::actingAs($user)
        ->test(LogVolunteerActivity::class, ['volunteer' => $volunteer])
        ->set('activityTypeId', $activityType->id)
        ->set('notes', 'Covered algebra')
        ->call('logActivity')
        ->assertDispatched('volunteer-changed');

    $activity = VolunteerActivity::where('volunteer_id', $volunteer->id)->first();
    expect($activity)->not->toBeNull();
    expect($activity->volunteer_attendance_id)->toBe($attendance->id);
    expect($activity->notes)->toBe('Covered algebra');
});

test('logActivity attaches selected students to the activity', function () {
    $user = User::factory()->create();
    $volunteer = checkedInVolunteer();
    $activityType = ActivityType::create(['name' => 'Tutoring']);
    $student = Student::create(['name' => 'Student One', 'dob' => '2012-01-01', 'gender' => 'male']);

    Livewire::actingAs($user)
        ->test(LogVolunteerActivity::class, ['volunteer' => $volunteer])
        ->set('activityTypeId', $activityType->id)
        ->set('studentIds', [$student->id])
        ->call('logActivity');

    $activity = VolunteerActivity::where('volunteer_id', $volunteer->id)->first();
    expect($activity->students->pluck('id'))->toContain($student->id);
});

test('logActivity allows zero students for a group session', function () {
    $user = User::factory()->create();
    $volunteer = checkedInVolunteer();
    $activityType = ActivityType::create(['name' => 'STEM Club']);

    Livewire::actingAs($user)
        ->test(LogVolunteerActivity::class, ['volunteer' => $volunteer])
        ->set('activityTypeId', $activityType->id)
        ->call('logActivity')
        ->assertHasNoErrors();

    $activity = VolunteerActivity::where('volunteer_id', $volunteer->id)->first();
    expect($activity)->not->toBeNull();
    expect($activity->students)->toHaveCount(0);
});

test('logActivity requires an activity type', function () {
    $user = User::factory()->create();
    $volunteer = checkedInVolunteer();

    Livewire::actingAs($user)
        ->test(LogVolunteerActivity::class, ['volunteer' => $volunteer])
        ->call('logActivity')
        ->assertHasErrors(['activityTypeId']);

    expect(VolunteerActivity::where('volunteer_id', $volunteer->id)->exists())->toBeFalse();
});

test('logActivity fails gracefully if the volunteer is not currently checked in', function () {
    $user = User::factory()->create();
    $volunteer = Volunteer::create(['name' => 'Not Checked In', 'status' => 'active']);
    $activityType = ActivityType::create(['name' => 'Tutoring']);

    Livewire::actingAs($user)
        ->test(LogVolunteerActivity::class, ['volunteer' => $volunteer])
        ->set('activityTypeId', $activityType->id)
        ->call('logActivity')
        ->assertDispatched('MessageChanged');

    expect(VolunteerActivity::where('volunteer_id', $volunteer->id)->exists())->toBeFalse();
});
