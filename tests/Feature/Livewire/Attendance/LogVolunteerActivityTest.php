<?php

use App\Livewire\Attendance\LogVolunteerActivity;
use App\Models\ActivityType;
use App\Models\Attendance;
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

function presentStudent(string $name = 'Student One'): Student
{
    $student = Student::create(['name' => $name, 'dob' => '2012-01-01', 'gender' => 'male']);
    Attendance::create(['student_id' => $student->id, 'date' => now(), 'current_in' => true]);

    return $student;
}

test('logActivity creates a volunteer activity tied to the currently open attendance visit', function () {
    $user = User::factory()->create();
    $volunteer = checkedInVolunteer();
    $activityType = ActivityType::create(['name' => 'Tutoring']);
    $attendance = VolunteerAttendance::where('volunteer_id', $volunteer->id)->first();

    Livewire::actingAs($user)
        ->test(LogVolunteerActivity::class, ['volunteer' => $volunteer])
        ->set('activityTypeIds', [$activityType->id])
        ->set('notes', 'Covered algebra')
        ->call('logActivity')
        ->assertDispatched('volunteer-changed');

    $activity = VolunteerActivity::where('volunteer_id', $volunteer->id)->first();
    expect($activity)->not->toBeNull();
    expect($activity->volunteer_attendance_id)->toBe($attendance->id);
    expect($activity->notes)->toBe('Covered algebra');
});

test('logActivity creates one activity per selected activity type', function () {
    $user = User::factory()->create();
    $volunteer = checkedInVolunteer();
    $tutoring = ActivityType::create(['name' => 'Tutoring']);
    $stemClub = ActivityType::create(['name' => 'STEM Club']);

    Livewire::actingAs($user)
        ->test(LogVolunteerActivity::class, ['volunteer' => $volunteer])
        ->set('activityTypeIds', [$tutoring->id, $stemClub->id])
        ->call('logActivity')
        ->assertHasNoErrors();

    $activities = VolunteerActivity::where('volunteer_id', $volunteer->id)->get();
    expect($activities)->toHaveCount(2);
    expect($activities->pluck('activity_type_id')->sort()->values()->all())
        ->toBe(collect([$tutoring->id, $stemClub->id])->sort()->values()->all());
});

test('logActivity attaches selected students to every created activity', function () {
    $user = User::factory()->create();
    $volunteer = checkedInVolunteer();
    $tutoring = ActivityType::create(['name' => 'Tutoring']);
    $stemClub = ActivityType::create(['name' => 'STEM Club']);
    $student = presentStudent();

    Livewire::actingAs($user)
        ->test(LogVolunteerActivity::class, ['volunteer' => $volunteer])
        ->set('activityTypeIds', [$tutoring->id, $stemClub->id])
        ->set('studentIds', [$student->id])
        ->call('logActivity');

    $activities = VolunteerActivity::where('volunteer_id', $volunteer->id)->get();
    expect($activities)->toHaveCount(2);
    $activities->each(fn ($activity) => expect($activity->students->pluck('id'))->toContain($student->id));
});

test('eligibleStudents only lists students with an attendance record today', function () {
    $user = User::factory()->create();
    $volunteer = checkedInVolunteer();
    $present = presentStudent('Present Student');
    $absent = Student::create(['name' => 'Absent Student', 'dob' => '2012-01-01', 'gender' => 'female']);

    $component = Livewire::actingAs($user)->test(LogVolunteerActivity::class, ['volunteer' => $volunteer]);

    $ids = $component->instance()->eligibleStudents()->pluck('id');
    expect($ids)->toContain($present->id);
    expect($ids)->not->toContain($absent->id);
});

test('logActivity allows zero students for a group session', function () {
    $user = User::factory()->create();
    $volunteer = checkedInVolunteer();
    $activityType = ActivityType::create(['name' => 'STEM Club']);

    Livewire::actingAs($user)
        ->test(LogVolunteerActivity::class, ['volunteer' => $volunteer])
        ->set('activityTypeIds', [$activityType->id])
        ->call('logActivity')
        ->assertHasNoErrors();

    $activity = VolunteerActivity::where('volunteer_id', $volunteer->id)->first();
    expect($activity)->not->toBeNull();
    expect($activity->students)->toHaveCount(0);
});

test('logActivity requires at least one activity type', function () {
    $user = User::factory()->create();
    $volunteer = checkedInVolunteer();

    Livewire::actingAs($user)
        ->test(LogVolunteerActivity::class, ['volunteer' => $volunteer])
        ->call('logActivity')
        ->assertHasErrors(['activityTypeIds']);

    expect(VolunteerActivity::where('volunteer_id', $volunteer->id)->exists())->toBeFalse();
});

test('logActivity fails gracefully if the volunteer is not currently checked in', function () {
    $user = User::factory()->create();
    $volunteer = Volunteer::create(['name' => 'Not Checked In', 'status' => 'active']);
    $activityType = ActivityType::create(['name' => 'Tutoring']);

    Livewire::actingAs($user)
        ->test(LogVolunteerActivity::class, ['volunteer' => $volunteer])
        ->set('activityTypeIds', [$activityType->id])
        ->call('logActivity')
        ->assertDispatched('MessageChanged');

    expect(VolunteerActivity::where('volunteer_id', $volunteer->id)->exists())->toBeFalse();
});
