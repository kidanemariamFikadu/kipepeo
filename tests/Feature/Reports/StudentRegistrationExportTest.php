<?php

use App\Exports\StudentRegistrationExport;
use App\Livewire\StudentList;
use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\School;
use App\Models\SchoolStudent;
use App\Models\Student;
use App\Models\User;
use App\Services\Reports\StudentRosterQuery;
use Carbon\Carbon;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;

function registeredStudent(
    string $name,
    string $createdAt,
    ?Grade $grade = null,
    ?School $school = null,
): Student {
    $student = Student::create(['name' => $name, 'dob' => '2015-01-01', 'gender' => 'female']);
    $student->forceFill(['created_at' => $createdAt])->save();

    if ($grade) {
        GradeStudent::create(['student_id' => $student->id, 'grade' => $grade->id, 'is_current' => true]);
    }

    if ($school) {
        SchoolStudent::create(['student_id' => $student->id, 'school_id' => $school->id, 'is_current' => true]);
    }

    return $student;
}

function rosterFilters(array $overrides = []): array
{
    return array_merge(['search' => '', 'school' => '', 'sortBy' => 'name', 'sortDir' => 'ASC'], $overrides);
}

function registrationRows(array $filters): array
{
    $export = new StudentRegistrationExport($filters);

    return $export->query()->get()->map(fn ($s) => $export->map($s))->all();
}

test('the export writes name, grade and the day the record was created', function () {
    $grade = Grade::create(['grade' => 'Grade 5']);
    registeredStudent('Amina Hassan', '2026-02-14 10:30:00', $grade);

    $export = new StudentRegistrationExport(rosterFilters());

    expect($export->headings())->toBe(['Name', 'Grade', 'Date Registered on System']);

    $rows = registrationRows(rosterFilters());
    expect($rows)->toHaveCount(1);

    [$name, $gradeLabel, $registered] = $rows[0];
    expect($name)->toBe('Amina Hassan');
    expect($gradeLabel)->toBe('Grade 5');

    // A real date cell, so the column sorts and groups as a date.
    expect($registered)->toBeNumeric();
    expect(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($registered)->format('Y-m-d'))
        ->toBe('2026-02-14');
});

test('the heading does not call it a join date, because it is not one', function () {
    // Every child imported in one go carries the date of that import. The
    // column name has to say "on System" so nobody reads it as the day a
    // child actually joined the centre.
    $heading = (new StudentRegistrationExport(rosterFilters()))->headings()[2];

    expect($heading)->toContain('on System');
    expect(strtolower($heading))->not->toContain('joined');
    expect(strtolower($heading))->not->toContain('enrolled');
});

test('a child with no grade is labelled rather than left blank', function () {
    registeredStudent('No Grade Child', '2026-02-14 09:00:00');

    expect(registrationRows(rosterFilters())[0][1])->toBe('Unassigned');
});

test('the export covers exactly the children the list is showing', function () {
    $riverside = School::create(['name' => 'Riverside Primary']);
    $hilltop = School::create(['name' => 'Hilltop Academy']);
    $grade = Grade::create(['grade' => 'Grade 5']);

    registeredStudent('Riverside Child', '2026-02-01 09:00:00', $grade, $riverside);
    registeredStudent('Hilltop Child', '2026-02-02 09:00:00', $grade, $hilltop);

    $filters = rosterFilters(['school' => $riverside->id]);

    $screen = Livewire::actingAs(User::factory()->create())
        ->test(StudentList::class)
        ->set('school', $riverside->id);

    $rows = registrationRows($filters);

    expect($rows)->toHaveCount(1);
    expect($rows[0][0])->toBe('Riverside Child');
    expect(count($rows))->toBe($screen->viewData('students')->total());
});

test('the search box narrows the export too', function () {
    registeredStudent('Amina Hassan', '2026-02-01 09:00:00');
    registeredStudent('Brian Otieno', '2026-02-02 09:00:00');

    $rows = registrationRows(rosterFilters(['search' => 'Amina']));

    expect($rows)->toHaveCount(1);
    expect($rows[0][0])->toBe('Amina Hassan');
});

test('graduated and deleted children are left out, as they are on the list', function () {
    $stays = registeredStudent('Still Here', '2026-02-01 09:00:00');
    $graduated = registeredStudent('Graduated Child', '2026-02-02 09:00:00');
    $deleted = registeredStudent('Deleted Child', '2026-02-03 09:00:00');

    expect(registrationRows(rosterFilters()))->toHaveCount(3);

    $graduated->update(['graduated_at' => Carbon::parse('2026-03-01')]);
    $deleted->delete();

    $rows = registrationRows(rosterFilters());

    expect($rows)->toHaveCount(1);
    expect($rows[0][0])->toBe('Still Here');
});

test('the export follows the sort chosen on the list', function () {
    registeredStudent('Zoe Last', '2026-01-01 09:00:00');
    registeredStudent('Adam First', '2026-06-01 09:00:00');

    expect(array_column(registrationRows(rosterFilters()), 0))
        ->toBe(['Adam First', 'Zoe Last']);

    // Sorting the list by registration date reorders the file the same way.
    expect(array_column(registrationRows(rosterFilters(['sortBy' => 'created_at', 'sortDir' => 'ASC'])), 0))
        ->toBe(['Zoe Last', 'Adam First']);
});

test('an arbitrary sort column cannot reach the query through the export', function () {
    registeredStudent('Some Child', '2026-02-01 09:00:00');

    // safeSortBy() is what the component passes, so a tampered property
    // falls back to the first allowed column rather than reaching SQL.
    $component = Livewire::actingAs(User::factory()->create())
        ->test(StudentList::class)
        ->set('sortBy', 'password');

    Excel::fake();
    $component->call('exportRegistrations');

    Excel::assertDownloaded(StudentRosterQuery::filename(rosterFilters()));
});

test('the button downloads a file named for the day it was taken', function () {
    Excel::fake();

    registeredStudent('Some Child', '2026-02-01 09:00:00');

    Livewire::actingAs(User::factory()->create())
        ->test(StudentList::class)
        ->call('exportRegistrations');

    Excel::assertDownloaded('student-registrations-'.now()->toDateString().'.xlsx');
});

test('a filtered export is named so it is not mistaken for the whole roster', function () {
    Excel::fake();

    $school = School::create(['name' => 'Riverside Primary']);
    registeredStudent('Riverside Child', '2026-02-01 09:00:00', null, $school);

    Livewire::actingAs(User::factory()->create())
        ->test(StudentList::class)
        ->set('school', $school->id)
        ->call('exportRegistrations');

    Excel::assertDownloaded('student-registrations-'.now()->toDateString().'-filtered.xlsx');
});

test('the students list still renders after moving to the shared query', function () {
    $school = School::create(['name' => 'Riverside Primary']);
    $grade = Grade::create(['grade' => 'Grade 5']);
    registeredStudent('Amina Hassan', '2026-02-01 09:00:00', $grade, $school);

    Livewire::actingAs(User::factory()->create())
        ->test(StudentList::class)
        ->assertOk()
        ->assertSee('Amina Hassan')
        ->assertSee('Riverside Primary');
});

test('select all still picks up the filtered page after the refactor', function () {
    $riverside = School::create(['name' => 'Riverside Primary']);
    $elsewhere = School::create(['name' => 'Hilltop Academy']);

    $wanted = registeredStudent('Riverside Child', '2026-02-01 09:00:00', null, $riverside);
    registeredStudent('Hilltop Child', '2026-02-02 09:00:00', null, $elsewhere);

    Livewire::actingAs(User::factory()->create(['role' => 'admin']))
        ->test(StudentList::class)
        ->set('school', $riverside->id)
        ->call('toggleSelectAll')
        ->assertSet('selectedStudents', [$wanted->id]);
});
