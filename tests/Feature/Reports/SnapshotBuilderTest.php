<?php

use App\Livewire\Report\AttendanceReport;
use App\Models\Attendance;
use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\School;
use App\Models\SchoolStudent;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\User;
use App\Services\Reports\SnapshotBuilder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

/**
 * A fixed Monday-to-Friday window, so the weekday denominator behind
 * attendance consistency does not change with the day the suite runs.
 */
const SNAP_FROM = '2026-03-02';

const SNAP_TO = '2026-03-06';

function snapStudent(
    string $name,
    string $dob,
    string $gender,
    ?School $school = null,
    ?Grade $grade = null,
    string $createdAt = '2026-01-15',
): Student {
    $student = Student::create(['name' => $name, 'dob' => $dob, 'gender' => $gender]);
    $student->forceFill(['created_at' => $createdAt])->save();

    if ($school) {
        SchoolStudent::create(['student_id' => $student->id, 'school_id' => $school->id, 'is_current' => true]);
    }

    if ($grade) {
        GradeStudent::create(['student_id' => $student->id, 'grade' => $grade->id, 'is_current' => true]);
    }

    StudentGuardian::create([
        'student_id' => $student->id,
        'guardian_name' => "Guardian of {$name}",
        'guardian_phone' => '0722123456',
        'is_primary' => true,
    ]);

    return $student;
}

function snapAttend(Student $student, string $date, int $seconds): Attendance
{
    return Attendance::create([
        'student_id' => $student->id,
        'date' => $date,
        'current_in' => false,
        'total_time' => $seconds,
    ]);
}

/**
 * A roster broad enough that every breakdown in the snapshot has more than
 * one bucket to sort, and attendance spread over several days.
 */
function snapSeed(): array
{
    $riverside = School::create(['name' => 'Riverside Primary']);
    $hilltop = School::create(['name' => 'Hilltop Academy']);
    $grade3 = Grade::create(['grade' => 'Grade 3']);
    $grade6 = Grade::create(['grade' => 'Grade 6']);

    $amina = snapStudent('Amina Hassan', '2016-04-02', 'female', $riverside, $grade3);
    $brian = snapStudent('Brian Otieno', '2013-09-11', 'male', $riverside, $grade6);
    $cynthia = snapStudent('Cynthia Wanjiru', '2011-01-20', 'female', $hilltop, $grade6);
    $david = snapStudent('David Kimani', '2016-07-30', 'male', $riverside, $grade3);

    // Amina attends every weekday, David four days, Brian three, Cynthia
    // once -- a spread of consistency rather than a flat block.
    //
    // The boys deliberately out-attend the girls (7 visits to 6) while a
    // girl is the lowest student id. Grouping follows first-encounter order,
    // so a snapshot that forgot to sort its breakdowns would still line up
    // with the screen if the two orders happened to agree. Here they don't.
    foreach (['2026-03-02', '2026-03-03', '2026-03-04', '2026-03-05', '2026-03-06'] as $date) {
        snapAttend($amina, $date, 3600);
    }
    foreach (['2026-03-02', '2026-03-04', '2026-03-06'] as $date) {
        snapAttend($brian, $date, 1800);
    }
    snapAttend($cynthia, '2026-03-03', 5400);
    snapAttend($david, '2026-03-02', 900);
    snapAttend($david, '2026-03-03', 2700);
    snapAttend($david, '2026-03-05', 1800);
    snapAttend($david, '2026-03-06', 1800);

    // Outside the window on both sides, so the date filter has something to
    // exclude. The earlier one also falls in the comparison period.
    snapAttend($amina, '2026-02-26', 3600);
    snapAttend($brian, '2026-03-09', 3600);

    return compact('riverside', 'hilltop', 'grade3', 'grade6', 'amina', 'brian', 'cynthia', 'david');
}

function snapBuild(bool $suppressed = false, bool $withComparison = true): array
{
    $builder = $suppressed ? new SnapshotBuilder(suppressBelow: 5) : SnapshotBuilder::unsuppressed();

    return $builder->build(Carbon::parse(SNAP_FROM), Carbon::parse(SNAP_TO), $withComparison);
}

beforeEach(function () {
    // Inside the period's month, so "enrolled as at period end" has a
    // present to be measured against.
    $this->travelTo(Carbon::parse('2026-03-10 09:00:00'));
});

test('the snapshot agrees with the attendance screen it will be quoted beside', function () {
    snapSeed();

    $snapshot = snapBuild()['attendance'];

    $screen = Livewire::actingAs(User::factory()->create(['role' => 'admin']))
        ->test(AttendanceReport::class)
        ->set('fromDate', SNAP_FROM)
        ->set('toDate', SNAP_TO)
        ->call('filter');

    // The screen's "totalStudents" counts attendance rows, not children --
    // the snapshot names that figure "visits" instead, but it must be the
    // same number or a generated report contradicts the page beside it.
    expect($snapshot['visits'])->toBe($screen->get('totalStudents'));
    expect($snapshot['average_visit_seconds'])->toBe((int) round($screen->get('averageAttendanceDuration')));

    expect($snapshot['visits_by_gender'])->toBe(collect($screen->get('studentsByGender'))->all());
    expect($snapshot['visits_by_grade'])->toBe(collect($screen->get('studentsByGrade'))->all());
    expect($snapshot['visits_by_school'])->toBe(collect($screen->get('studentsBySchool'))->all());
    expect($snapshot['unique_students_by_age_band'])->toBe(collect($screen->get('studentsByAge'))->all());

    // The daily series must line up day for day.
    $screenDaily = collect($screen->get('dailyStatistics'))
        ->mapWithKeys(fn ($day) => [$day['date'] => $day['totalStudents']])
        ->all();
    $snapshotDaily = collect($snapshot['daily'])
        ->mapWithKeys(fn ($day) => [$day['date'] => $day['visits']])
        ->all();

    expect($snapshotDaily)->toBe($screenDaily);
});

test('the snapshot counts what it says it counts', function () {
    snapSeed();

    $attendance = snapBuild()['attendance'];

    expect($attendance['visits'])->toBe(13);            // 5 + 3 + 1 + 4
    expect($attendance['unique_students'])->toBe(4);
    expect($attendance['daily'])->toHaveCount(5);

    // Sorted by size, not by the order the children were encountered.
    expect($attendance['visits_by_gender'])->toBe(['Male' => 7, 'Female' => 6]);
    expect($attendance['visits_by_school'])->toBe(['Riverside Primary' => 12, 'Hilltop Academy' => 1]);

    // Amina 5/5 weekdays, David 4/5, Brian 3/5, Cynthia 1/5 -> 100/80/60/20,
    // median of the four is 70.
    expect($attendance['consistency_percent']['median'])->toBe(70.0);
    expect($attendance['consistency_percent']['students_at_or_above_80'])->toBe(2);
    expect($attendance['consistency_percent']['students_below_40'])->toBe(1);
});

test('no child can be identified from the snapshot', function () {
    $seeded = snapSeed();

    // Suppression off is the worse case for privacy, so test that one.
    $payload = json_encode(snapBuild(), JSON_THROW_ON_ERROR);

    foreach (Student::withTrashed()->get() as $student) {
        expect($payload)->not->toContain($student->name);
        expect($payload)->not->toContain((string) $student->dob);
    }

    foreach (StudentGuardian::all() as $guardian) {
        expect($payload)->not->toContain($guardian->guardian_name);
        expect($payload)->not->toContain($guardian->guardian_phone);
    }

    // A first name on its own would be just as identifying as the full one.
    expect($payload)->not->toContain('Amina');
    expect($payload)->not->toContain('Otieno');
    expect($payload)->not->toContain($seeded['amina']->dob);
});

test('suppression merges categories too small to be safe, without losing the total', function () {
    snapSeed();

    $open = snapBuild(suppressed: false)['attendance'];
    $safe = snapBuild(suppressed: true)['attendance'];

    // Hilltop has one attending child and Riverside three; naming either
    // beside its figure would point at those children. Riverside's twelve
    // visits must not buy it an exemption -- the threshold is about how many
    // children are behind the number, not how big the number is.
    expect($open['visits_by_school'])->toHaveKey('Hilltop Academy');
    expect($open['visits_by_school']['Riverside Primary'])->toBe(12);
    expect($safe['visits_by_school'])->not->toHaveKey('Hilltop Academy');
    expect($safe['visits_by_school'])->not->toHaveKey('Riverside Primary');

    // Nothing is lost in the merge: the figures still add up.
    expect(array_sum($safe['visits_by_school']))->toBe(array_sum($open['visits_by_school']));
    expect($safe['visits_by_school'])->toHaveKey('Other (2 categories with fewer than 5 children)');
});

test('attendance belonging to a deleted child is excluded, as it is on screen', function () {
    $seeded = snapSeed();

    $before = snapBuild()['attendance'];
    $seeded['amina']->delete();
    $after = snapBuild()['attendance'];

    expect($before['visits'])->toBe(13);
    expect($after['visits'])->toBe(8);
    expect($after['unique_students'])->toBe(3);

    // And the screen agrees, which is the point of the exclusion.
    $screen = Livewire::actingAs(User::factory()->create(['role' => 'admin']))
        ->test(AttendanceReport::class)
        ->set('fromDate', SNAP_FROM)
        ->set('toDate', SNAP_TO)
        ->call('filter');

    expect($after['visits'])->toBe($screen->get('totalStudents'));
});

test('the comparison window is the equal-length period immediately before', function () {
    snapSeed();

    $snapshot = snapBuild();

    expect($snapshot['period'])->toMatchArray(['from' => '2026-03-02', 'to' => '2026-03-06', 'days' => 5, 'weekdays' => 5]);
    expect($snapshot['previous_period']['period'])->toMatchArray(['from' => '2026-02-25', 'to' => '2026-03-01', 'days' => 5]);

    // The one visit seeded on 26 Feb lands in the comparison window.
    expect($snapshot['previous_period']['attendance']['visits'])->toBe(1);

    // The comparison carries no daily series -- it exists to be compared
    // against, not charted.
    expect($snapshot['previous_period']['attendance'])->not->toHaveKey('daily');
});

test('enrollment is counted as it stood at the end of the period', function () {
    $seeded = snapSeed();

    expect(snapBuild()['enrollment']['total_active'])->toBe(4);

    // A child who graduated after the period still counts within it.
    $seeded['cynthia']->update(['graduated_at' => Carbon::parse('2026-03-09')]);
    expect(snapBuild()['enrollment']['total_active'])->toBe(4);

    // One who graduated during it does not.
    $seeded['cynthia']->update(['graduated_at' => Carbon::parse('2026-03-04')]);
    expect(snapBuild()['enrollment']['total_active'])->toBe(3);

    // Nor does one enrolled only afterwards.
    snapStudent('Later Joiner', '2015-05-05', 'male', createdAt: '2026-04-01');
    expect(snapBuild()['enrollment']['total_active'])->toBe(3);
});

test('an empty period produces a usable snapshot rather than an error', function () {
    $snapshot = SnapshotBuilder::unsuppressed()
        ->build(Carbon::parse('2026-01-05'), Carbon::parse('2026-01-09'));

    expect($snapshot['attendance']['visits'])->toBe(0);
    expect($snapshot['attendance']['unique_students'])->toBe(0);
    expect($snapshot['attendance']['average_visit_seconds'])->toBeNull();
    expect($snapshot['attendance']['average_visit_minutes'])->toBeNull();
    expect($snapshot['attendance']['consistency_percent']['median'])->toBeNull();
    expect($snapshot['attendance']['daily'])->toBe([]);
    expect($snapshot['caveats'])->not->toBeEmpty();
});

test('building a snapshot does not issue a query per child', function () {
    snapSeed();

    DB::enableQueryLog();
    DB::flushQueryLog();
    snapBuild();
    $small = count(DB::getQueryLog());
    DB::disableQueryLog();

    foreach (range(1, 30) as $i) {
        $student = snapStudent("Extra {$i}", '2015-06-06', $i % 2 ? 'male' : 'female');
        snapAttend($student, '2026-03-03', 1800);
    }

    DB::enableQueryLog();
    DB::flushQueryLog();
    snapBuild();
    $large = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($large)->toBe($small);
});

test('a period that predates every student record says so instead of reporting zero', function () {
    snapSeed();

    // Every seeded child was first recorded on 2026-01-15, so January 5-9
    // has no roster to reconstruct.
    $snapshot = SnapshotBuilder::unsuppressed()
        ->build(Carbon::parse('2026-01-05'), Carbon::parse('2026-01-09'), withComparison: false);

    expect($snapshot['enrollment'])->toHaveKey('unavailable');
    expect($snapshot['enrollment'])->not->toHaveKey('total_active');
    expect($snapshot['enrollment']['unavailable'])->toContain('cannot be reconstructed');
});

test('category counts survive suppression, because the breakdown often does not', function () {
    snapSeed();

    $safe = snapBuild(suppressed: true);

    // Riverside has three attending children and Hilltop one, so with a
    // threshold of five the whole breakdown collapses -- which is exactly
    // the shape of this charity's real data.
    expect($safe['attendance']['visits_by_school'])->toHaveCount(1);
    expect(array_keys($safe['attendance']['visits_by_school'])[0])->toStartWith('Other (');

    // The count of schools is still reportable, and is the more useful fact.
    expect($safe['attendance']['distinct_schools_attending'])->toBe(2);
    expect($safe['attendance']['distinct_grades_attending'])->toBe(2);
    expect($safe['enrollment']['distinct_schools'])->toBe(2);
});

test('the snapshot reports arrivals and graduations within the period', function () {
    $seeded = snapSeed();

    expect(snapBuild()['enrollment']['first_recorded_in_period'])->toBe(0);
    expect(snapBuild()['enrollment']['graduated_in_period'])->toBe(0);

    snapStudent('New Arrival', '2017-02-02', 'female', createdAt: '2026-03-03');
    $seeded['brian']->update(['graduated_at' => Carbon::parse('2026-03-04')]);

    $enrollment = snapBuild()['enrollment'];
    expect($enrollment['first_recorded_in_period'])->toBe(1);
    expect($enrollment['graduated_in_period'])->toBe(1);
});

test('one child attending often does not pass suppression as a big number', function () {
    // The flaw this guards: suppression used to compare the reported figure
    // against the threshold. A lone child attending twenty times produced
    // "Lone Star Academy: 20", which clears a threshold of five while
    // identifying exactly one child.
    $lonely = School::create(['name' => 'Lone Star Academy']);
    $onlyChild = snapStudent('Solo Mwangi', '2014-08-08', 'male', $lonely);

    foreach (['2026-03-02', '2026-03-03', '2026-03-04', '2026-03-05', '2026-03-06'] as $date) {
        snapAttend($onlyChild, $date, 3600);
    }

    // Plus a school with enough children to legitimately survive.
    $busy = School::create(['name' => 'Busy Primary']);
    foreach (range(1, 6) as $i) {
        snapAttend(snapStudent("Busy Child {$i}", '2015-03-03', 'female', $busy), '2026-03-02', 3600);
    }

    $safe = snapBuild(suppressed: true)['attendance'];

    expect($safe['visits_by_school'])->not->toHaveKey('Lone Star Academy');
    // Six children, six visits -- fewer visits than the lone child, yet this
    // is the one that is safe to name.
    expect($safe['visits_by_school'])->toHaveKey('Busy Primary');
    expect($safe['visits_by_school']['Busy Primary'])->toBe(6);
});
