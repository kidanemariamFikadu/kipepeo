<?php

use App\Exports\AttendanceExport;
use App\Livewire\Report\AttendanceReport;
use App\Models\Attendance;
use App\Models\AttendanceAttr;
use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\Student;
use App\Models\User;
use App\Services\Reports\AttendanceQuery;
use Carbon\Carbon;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;

const EXPORT_FROM = '2026-03-02';

const EXPORT_TO = '2026-03-06';

function exportStudent(string $name, string $gender = 'female', ?Grade $grade = null): Student
{
    $student = Student::create(['name' => $name, 'dob' => '2015-01-01', 'gender' => $gender]);

    if ($grade) {
        GradeStudent::create(['student_id' => $student->id, 'grade' => $grade->id, 'is_current' => true]);
    }

    return $student;
}

function exportVisit(Student $student, string $date, ?string $in = '08:00', ?string $out = '12:30'): Attendance
{
    $attendance = Attendance::create([
        'student_id' => $student->id,
        'date' => $date,
        'current_in' => $out === null,
        'total_time' => 16200,
    ]);

    AttendanceAttr::create([
        'attendance_id' => $attendance->id,
        'student_id' => $student->id,
        'date' => $date,
        'time_in' => $in,
        'time_out' => $out,
    ]);

    return $attendance;
}

/** The rows the export would write, headings excluded. */
function exportRows(array $filters): array
{
    $export = new AttendanceExport($filters);

    return $export->query()->get()
        ->flatMap(fn ($attendance) => $export->map($attendance))
        ->all();
}

function defaultFilters(array $overrides = []): array
{
    return array_merge([
        'fromDate' => EXPORT_FROM,
        'toDate' => EXPORT_TO,
        'studentId' => '',
        'gender' => '',
        'gradeId' => '',
    ], $overrides);
}

test('the export writes name, grade, date, time in and time out', function () {
    $grade = Grade::create(['grade' => 'Grade 4']);
    $student = exportStudent('Amina Hassan', 'female', $grade);
    exportVisit($student, '2026-03-03', '08:15', '13:45');

    $export = new AttendanceExport(defaultFilters());

    expect($export->headings())->toBe(['Name', 'Grade', 'Date', 'Time In', 'Time Out']);

    $rows = exportRows(defaultFilters());
    expect($rows)->toHaveCount(1);

    [$name, $gradeLabel, $date, $in, $out] = $rows[0];
    expect($name)->toBe('Amina Hassan');
    expect($gradeLabel)->toBe('Grade 4');
    expect($in)->toBe('08:15');
    expect($out)->toBe('13:45');

    // A real date cell, not text, so Excel can group it by month.
    expect($date)->toBeNumeric();
    expect(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date)->format('Y-m-d'))->toBe('2026-03-03');
});

test('a child still signed in has an empty time out, not an invented one', function () {
    $student = exportStudent('Brian Otieno', 'male');
    exportVisit($student, '2026-03-03', '09:00', null);

    $rows = exportRows(defaultFilters());

    expect($rows)->toHaveCount(1);
    expect($rows[0][3])->toBe('09:00');
    expect($rows[0][4])->toBeNull();
});

test('a child with no grade is labelled rather than left blank', function () {
    $student = exportStudent('Cynthia Wanjiru');
    exportVisit($student, '2026-03-03');

    expect(exportRows(defaultFilters())[0][1])->toBe('Unassigned');
});

test('the export covers exactly the rows the screen is showing', function () {
    $grade = Grade::create(['grade' => 'Grade 4']);
    $other = Grade::create(['grade' => 'Grade 9']);

    $inScope = exportStudent('In Scope', 'female', $grade);
    $wrongGrade = exportStudent('Wrong Grade', 'female', $other);
    $wrongGender = exportStudent('Wrong Gender', 'male', $grade);

    foreach ([$inScope, $wrongGrade, $wrongGender] as $student) {
        exportVisit($student, '2026-03-03');
    }

    // Outside the date range entirely.
    exportVisit($inScope, '2026-03-20');

    $filters = defaultFilters(['gender' => 'female', 'gradeId' => $grade->id]);

    $screen = Livewire::actingAs(User::factory()->create())
        ->test(AttendanceReport::class)
        ->set('fromDate', EXPORT_FROM)
        ->set('toDate', EXPORT_TO)
        ->set('gender', 'female')
        ->set('gradeId', $grade->id)
        ->call('filter');

    $rows = exportRows($filters);

    // One visit, matching the single visit the page counts.
    expect($rows)->toHaveCount(1);
    expect($rows[0][0])->toBe('In Scope');
    expect(count($rows))->toBe($screen->get('totalStudents'));
});

test('attendance belonging to a deleted child is left out, as it is on screen', function () {
    $student = exportStudent('Deleted Child');
    exportVisit($student, '2026-03-03');
    exportVisit(exportStudent('Kept Child'), '2026-03-03');

    expect(exportRows(defaultFilters()))->toHaveCount(2);

    $student->delete();

    $rows = exportRows(defaultFilters());
    expect($rows)->toHaveCount(1);
    expect($rows[0][0])->toBe('Kept Child');
});

test('a second visit on the same day is its own row, never dropped', function () {
    $student = exportStudent('Returns Later');
    $attendance = exportVisit($student, '2026-03-03', '08:00', '10:00');

    // Signed out and back in: a second pair against the same attendance day.
    AttendanceAttr::create([
        'attendance_id' => $attendance->id,
        'student_id' => $student->id,
        'date' => '2026-03-03',
        'time_in' => '14:00',
        'time_out' => '16:00',
    ]);

    $rows = exportRows(defaultFilters());

    expect($rows)->toHaveCount(2);
    expect(array_column($rows, 3))->toBe(['08:00', '14:00']);
});

test('a child marked present with no recorded times still appears', function () {
    $student = exportStudent('No Times');
    Attendance::create([
        'student_id' => $student->id,
        'date' => '2026-03-03',
        'current_in' => false,
        'total_time' => 0,
    ]);

    $rows = exportRows(defaultFilters());

    expect($rows)->toHaveCount(1);
    expect($rows[0][0])->toBe('No Times');
    expect($rows[0][3])->toBeNull();
    expect($rows[0][4])->toBeNull();
});

test('rows come out ordered by date then by name', function () {
    $zoe = exportStudent('Zoe Last');
    $adam = exportStudent('Adam First');

    exportVisit($zoe, '2026-03-03');
    exportVisit($adam, '2026-03-03');
    exportVisit($zoe, '2026-03-02');

    $rows = exportRows(defaultFilters());

    expect(array_column($rows, 0))->toBe(['Zoe Last', 'Adam First', 'Zoe Last']);
});

test('the filename says which period and filter the file holds', function () {
    expect(AttendanceQuery::filename(defaultFilters()))
        ->toBe('attendance-2026-03-02-to-2026-03-06.xlsx');

    expect(AttendanceQuery::filename(defaultFilters(['gender' => 'female'])))
        ->toBe('attendance-2026-03-02-to-2026-03-06-female.xlsx');
});

test('the button downloads a file and refuses an impossible period', function () {
    Excel::fake();

    $component = Livewire::actingAs(User::factory()->create())
        ->test(AttendanceReport::class)
        ->set('fromDate', EXPORT_FROM)
        ->set('toDate', EXPORT_TO);

    $component->call('exportExcel');
    Excel::assertDownloaded('attendance-2026-03-02-to-2026-03-06.xlsx');

    // An end date before the start would otherwise produce an empty file
    // with no hint as to why.
    $component->set('toDate', '2026-02-01')
        ->call('exportExcel')
        ->assertHasErrors('toDate');
});
