<?php

use App\Livewire\Report\NarrativeReport;
use App\Models\Attendance;
use App\Models\School;
use App\Models\SchoolStudent;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

function narrativeChild(string $name, School $school, int $visits, string $firstDate = '2026-03-02'): Student
{
    $student = Student::create(['name' => $name, 'dob' => '2015-05-05', 'gender' => 'female']);
    $student->forceFill(['created_at' => '2026-01-01'])->save();
    SchoolStudent::create(['student_id' => $student->id, 'school_id' => $school->id, 'is_current' => true]);
    StudentGuardian::create([
        'student_id' => $student->id,
        'guardian_name' => "Guardian of {$name}",
        'guardian_phone' => '0733444555',
        'is_primary' => true,
    ]);

    $date = Carbon::parse($firstDate);
    foreach (range(1, $visits) as $i) {
        Attendance::create([
            'student_id' => $student->id,
            'date' => $date->copy()->addDays($i - 1)->toDateString(),
            'current_in' => false,
            'total_time' => 3600,
        ]);
    }

    return $student;
}

function narrativeAdmin(): User
{
    return User::factory()->create(['role' => 'admin']);
}

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-04-10 09:00:00'));
});

test('only admins can open the narrative report', function () {
    $this->actingAs(User::factory()->create(['role' => 'user']))
        ->get('/reports/narrative')
        ->assertForbidden();

    $this->flushSession();

    $this->actingAs(narrativeAdmin())
        ->get('/reports/narrative')
        ->assertOk()
        ->assertSee('Narrative Report');
});

test('the period defaults to the last complete month', function () {
    Livewire::actingAs(narrativeAdmin())
        ->test(NarrativeReport::class)
        ->assertSet('fromDate', '2026-03-01')
        ->assertSet('toDate', '2026-03-31');
});

test('the page states what would be sent, and never sends a name', function () {
    $school = School::create(['name' => 'Riverside Primary']);
    narrativeChild('Amina Hassan', $school, 4);

    $html = Livewire::actingAs(narrativeAdmin())
        ->test(NarrativeReport::class)
        ->set('fromDate', '2026-03-01')
        ->set('toDate', '2026-03-31')
        ->html();

    expect($html)->toContain('What would be sent');

    // The rendered page is itself a disclosure surface: if a name reached
    // the payload it would be printed here in the preview.
    expect($html)->not->toContain('Amina');
    expect($html)->not->toContain('Guardian of');
    expect($html)->not->toContain('0733444555');
});

test('the donor report merges small groups and the staff report does not', function () {
    $tiny = School::create(['name' => 'Lone Star Academy']);
    $big = School::create(['name' => 'Busy Primary']);

    narrativeChild('Solo Child', $tiny, 6);
    foreach (range(1, 6) as $i) {
        narrativeChild("Busy Child {$i}", $big, 1);
    }

    $component = Livewire::actingAs(narrativeAdmin())
        ->test(NarrativeReport::class)
        ->set('fromDate', '2026-03-01')
        ->set('toDate', '2026-03-31');

    // Donor: the one-child school is merged away, the six-child one stays.
    $donor = $component->set('reportType', 'donor')->html();
    expect($donor)->not->toContain('Lone Star Academy');
    expect($donor)->toContain('Busy Primary');
    expect($donor)->toContain('Small groups are merged');

    // Staff: shown in full, with the warning that it must not leave.
    $staff = $component->set('reportType', 'staff')->html();
    expect($staff)->toContain('Lone Star Academy');
    expect($staff)->toContain('do not paste these figures into');
});

test('an inverted period asks for a usable one instead of erroring', function () {
    Livewire::actingAs(narrativeAdmin())
        ->test(NarrativeReport::class)
        ->set('fromDate', '2026-03-31')
        ->set('toDate', '2026-03-01')
        ->assertHasErrors('toDate')
        ->assertSee('start date on or before its end date')
        ->assertOk();
});

test('the quick period buttons set a whole month and a three-month run', function () {
    $component = Livewire::actingAs(narrativeAdmin())->test(NarrativeReport::class);

    $component->call('setLastFullMonth')
        ->assertSet('fromDate', '2026-03-01')
        ->assertSet('toDate', '2026-03-31');

    $component->call('setThisTerm')
        ->assertSet('toDate', '2026-04-09')
        ->assertSet('fromDate', '2026-01-10');
});

test('a period with nothing to compare against says so rather than implying growth', function () {
    $school = School::create(['name' => 'Riverside Primary']);
    narrativeChild('Only Child', $school, 3);

    // March has attendance; February has none, so there is no comparison.
    $html = Livewire::actingAs(narrativeAdmin())
        ->test(NarrativeReport::class)
        ->set('fromDate', '2026-03-01')
        ->set('toDate', '2026-03-31')
        ->html();

    expect($html)->toContain('No comparable activity in the preceding period');
    expect($html)->toContain('but not say what changed');
});

test('generation is visibly not wired up, so nobody waits for a report that is not coming', function () {
    $html = Livewire::actingAs(narrativeAdmin())->test(NarrativeReport::class)->html();

    expect($html)->toContain('not connected yet');
    expect($html)->toContain('disabled');
});

test('the payload size is shown so the cost of a run is predictable', function () {
    $school = School::create(['name' => 'Riverside Primary']);
    narrativeChild('Some Child', $school, 5);

    $html = Livewire::actingAs(narrativeAdmin())
        ->test(NarrativeReport::class)
        ->set('fromDate', '2026-03-01')
        ->set('toDate', '2026-03-31')
        ->html();

    expect($html)->toContain('bytes, roughly');
    expect($html)->toContain('tokens');
});

test('every repeated row on the page is keyed', function () {
    $school = School::create(['name' => 'Riverside Primary']);
    narrativeChild('Some Child', $school, 5);

    $html = Livewire::actingAs(narrativeAdmin())
        ->test(NarrativeReport::class)
        ->set('fromDate', '2026-03-01')
        ->set('toDate', '2026-03-31')
        ->html();

    // The breakdown tables, the caveat list and the type options all loop.
    expect($html)->toContain('wire:key="breakdown-visits-by-gender"');
    expect($html)->toContain('wire:key="caveat-0"');
    expect($html)->toContain('wire:key="type-donor"');
});
