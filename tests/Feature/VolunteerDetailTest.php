<?php

use App\Models\ActivityType;
use App\Models\Student;
use App\Models\User;
use App\Models\Volunteer;
use App\Models\VolunteerActivity;
use App\Models\VolunteerAttendance;
use App\Models\VolunteerAttendanceAttr;
use Illuminate\Support\Facades\DB;
use Livewire\Drawer\Utils;

function makeVolunteer(string $name, array $overrides = []): Volunteer
{
    return Volunteer::create(array_merge([
        'name' => $name,
        'status' => 'active',
    ], $overrides));
}

// VolunteerDetail::mount()/render() read the volunteer id straight from
// request()->route('volunteer_id') rather than a mount() parameter (mirrors
// StudentDetail), so it can't be constructed through Livewire::test(). Instead,
// drive it the way a real browser would: load the real page, pull the
// wire:snapshot out of the HTML, and post that snapshot plus property updates
// and a method call to Livewire's actual update endpoint.
function callVolunteerDetailMethod(User $user, Volunteer $volunteer, string $method, array $updates = [], array $params = []): void
{
    $html = test()->actingAs($user)->get("/volunteer-detail/{$volunteer->id}")->getContent();
    $snapshot = Utils::extractAttributeDataFromHtml($html, 'wire:snapshot');

    test()->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
        ->actingAs($user)
        ->postJson('/livewire/update', [
            'components' => [[
                'snapshot' => json_encode($snapshot),
                'updates' => $updates,
                'calls' => [[
                    'path' => '',
                    'method' => $method,
                    'params' => $params,
                ]],
            ]],
        ]);
}

test('volunteer detail page shows the requested volunteer, not an unrelated one', function () {
    $user = User::factory()->create(['role' => 'user']);
    $first = makeVolunteer('Alpha Volunteer');
    $second = makeVolunteer('Beta Volunteer');

    $response = $this->actingAs($user)->get("/volunteer-detail/{$second->id}");

    $response->assertOk();
    $response->assertSee('Beta Volunteer');
    $response->assertDontSee('Alpha Volunteer');
});

test('volunteer detail page does not N+1 query per related row', function () {
    $user = User::factory()->create(['role' => 'user']);
    $volunteer = makeVolunteer('Query Count Volunteer');

    $queryCount = 0;
    DB::listen(function () use (&$queryCount) {
        $queryCount++;
    });

    $this->actingAs($user)->get("/volunteer-detail/{$volunteer->id}")->assertOk();

    expect($queryCount)->toBeLessThan(20);
});

test('requesting a non-existent volunteer 404s', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)->get('/volunteer-detail/999999')->assertNotFound();
});

test('the basic information form updates the volunteer', function () {
    $user = User::factory()->create(['role' => 'user']);
    $volunteer = makeVolunteer('Editable Volunteer', ['phone' => '111']);

    callVolunteerDetailMethod($user, $volunteer, 'update', [
        'name' => 'Renamed Volunteer',
        'phone' => '222',
        'status' => 'inactive',
    ]);

    $volunteer->refresh();
    expect($volunteer->name)->toBe('Renamed Volunteer');
    expect($volunteer->phone)->toBe('222');
    expect($volunteer->isActive())->toBeFalse();
});

test('volunteer detail page shows attendance history for the volunteer', function () {
    $user = User::factory()->create(['role' => 'user']);
    $volunteer = makeVolunteer('Attending Volunteer');

    $attendance = VolunteerAttendance::create([
        'volunteer_id' => $volunteer->id,
        'date' => '2026-06-01',
        'current_in' => false,
        'total_time' => 3661,
    ]);
    VolunteerAttendanceAttr::create([
        'volunteer_attendance_id' => $attendance->id,
        'volunteer_id' => $volunteer->id,
        'date' => '2026-06-01',
        'time_in' => '2026-06-01 08:00:00',
        'time_out' => '2026-06-01 09:00:00',
    ]);

    $response = $this->actingAs($user)->get("/volunteer-detail/{$volunteer->id}");

    $response->assertSee('Attendance History');
    $response->assertSee('2026-06-01');
    $response->assertSee('01:01:01');
});

test('volunteer detail page shows the activity log for the volunteer', function () {
    $user = User::factory()->create(['role' => 'user']);
    $volunteer = makeVolunteer('Active Duty Volunteer');
    $student = Student::create(['name' => 'Helped Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    $activityType = ActivityType::create(['name' => 'Mentorship']);
    $attendance = VolunteerAttendance::create(['volunteer_id' => $volunteer->id, 'date' => now(), 'current_in' => false]);
    $activity = VolunteerActivity::create([
        'volunteer_attendance_id' => $attendance->id,
        'volunteer_id' => $volunteer->id,
        'activity_type_id' => $activityType->id,
        'date' => now(),
        'notes' => 'Solid progress',
    ]);
    $activity->students()->attach($student->id);

    $response = $this->actingAs($user)->get("/volunteer-detail/{$volunteer->id}");

    $response->assertSee('Activity Log');
    $response->assertSee('Mentorship');
    $response->assertSee('Helped Student');
    $response->assertSee('Solid progress');
});

test('earnings summary shows a KSH estimate when an hourly rate is set', function () {
    $user = User::factory()->create(['role' => 'user']);
    $volunteer = makeVolunteer('Paid Volunteer', ['hourly_rate' => 500]);

    VolunteerAttendance::create([
        'volunteer_id' => $volunteer->id,
        'date' => now(),
        'current_in' => false,
        'total_time' => 7200,
    ]);

    $response = $this->actingAs($user)->get("/volunteer-detail/{$volunteer->id}");

    $response->assertSee('Earnings Summary');
    $response->assertSee('KSH 1,000.00');
});

test('earnings summary shows no amount when no hourly rate is set', function () {
    $user = User::factory()->create(['role' => 'user']);
    $volunteer = makeVolunteer('Unpaid Volunteer');

    VolunteerAttendance::create([
        'volunteer_id' => $volunteer->id,
        'date' => now(),
        'current_in' => false,
        'total_time' => 7200,
    ]);

    $response = $this->actingAs($user)->get("/volunteer-detail/{$volunteer->id}");

    $response->assertSee('No hourly rate set');
});
