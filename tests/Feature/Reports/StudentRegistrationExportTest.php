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

    expect($export->headings())->toBe(['Name', 'Grade', 'Date Registered on System', 'Days Attended (All Time)']);

    $rows = registrationRows(rosterFilters());
    expect($rows)->toHaveCount(1);

    [$name, $gradeLabel, $registered, $daysAttended] = $rows[0];
    expect($daysAttended)->toBe(0);
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

test('the export counts the days each child came to the space', function () {
    $amina = registeredStudent('Amina Hassan', '2026-02-01 09:00:00');
    $brian = registeredStudent('Brian Otieno', '2026-02-02 09:00:00');

    foreach (['2026-03-02', '2026-03-03', '2026-03-09'] as $date) {
        \App\Models\Attendance::create([
            'student_id' => $amina->id, 'date' => $date, 'current_in' => false, 'total_time' => 3600,
        ]);
    }
    \App\Models\Attendance::create([
        'student_id' => $brian->id, 'date' => '2026-03-02', 'current_in' => false, 'total_time' => 3600,
    ]);

    $rows = registrationRows(rosterFilters());

    expect((new StudentRegistrationExport(rosterFilters()))->headings())
        ->toBe(['Name', 'Grade', 'Date Registered on System', 'Days Attended (All Time)']);

    expect($rows[0][0])->toBe('Amina Hassan');
    expect($rows[0][3])->toBe(3);
    expect($rows[1][0])->toBe('Brian Otieno');
    expect($rows[1][3])->toBe(1);
});

test('a child who has never attended shows zero, not a blank cell', function () {
    registeredStudent('Never Came', '2026-02-01 09:00:00');

    $row = registrationRows(rosterFilters())[0];

    // Blank would read as missing data; this child genuinely has none.
    expect($row[3])->toBe(0);
    expect($row[3])->not->toBeNull();
});

test('days attended counts days, not attendance rows', function () {
    $student = registeredStudent('Twice In A Day', '2026-02-01 09:00:00');

    // One attendance row per child per day is the rule today, but the count
    // must survive the day that stops being true.
    foreach (['2026-03-02', '2026-03-02', '2026-03-03'] as $date) {
        \App\Models\Attendance::create([
            'student_id' => $student->id, 'date' => $date, 'current_in' => false, 'total_time' => 3600,
        ]);
    }

    expect(registrationRows(rosterFilters())[0][3])->toBe(2);
});

test('counting the days does not add a query per child', function () {
    foreach (range(1, 12) as $i) {
        $student = registeredStudent("Child {$i}", '2026-02-01 09:00:00');
        \App\Models\Attendance::create([
            'student_id' => $student->id, 'date' => '2026-03-02', 'current_in' => false, 'total_time' => 3600,
        ]);
    }

    \Illuminate\Support\Facades\DB::enableQueryLog();
    \Illuminate\Support\Facades\DB::flushQueryLog();
    registrationRows(rosterFilters());
    $queries = count(\Illuminate\Support\Facades\DB::getQueryLog());
    \Illuminate\Support\Facades\DB::disableQueryLog();

    // The roster, its grades, and the counting subquery folded into the
    // roster query -- not one count per child.
    expect($queries)->toBeLessThan(5);
});

test('zero days survives into the written file as a 0, not a blank cell', function () {
    // Testing map() alone missed this: map returned 0 correctly, and the
    // file still came out blank. PhpSpreadsheet compares each value against
    // its null placeholder loosely, and 0 == null in PHP, so the cell was
    // skipped on the way to disk. Only reading the real file catches it.
    registeredStudent('Never Came', '2026-02-01 09:00:00');
    $attended = registeredStudent('Zz Came Once', '2026-02-01 09:00:00');
    \App\Models\Attendance::create([
        'student_id' => $attended->id, 'date' => '2026-03-02', 'current_in' => false, 'total_time' => 3600,
    ]);

    $path = storage_path('app/testing-registrations.xlsx');
    @unlink($path);

    \Maatwebsite\Excel\Facades\Excel::store(
        new StudentRegistrationExport(rosterFilters()),
        'testing-registrations.xlsx',
        'local'
    );

    $stored = storage_path('app/private/testing-registrations.xlsx');
    if (! file_exists($stored)) {
        $stored = $path;
    }

    $sheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($stored)->getActiveSheet();

    expect($sheet->getCell('A2')->getValue())->toBe('Never Came');
    expect($sheet->getCell('D2')->getValue())->not->toBeNull();
    expect((int) $sheet->getCell('D2')->getValue())->toBe(0);

    expect($sheet->getCell('D3')->getValue())->toBe(1);

    @unlink($stored);
});
