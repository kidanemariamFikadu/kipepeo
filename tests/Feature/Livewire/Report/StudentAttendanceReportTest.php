<?php

use App\Livewire\Report\StudentAttendance;
use App\Models\Attendance;
use App\Models\AttendanceAttr;
use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\School;
use App\Models\SchoolStudent;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\User;
use Livewire\Livewire;

test('getStudentByDate returns a formatted list of students attending on the given date', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Attending Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    $school = School::create(['name' => 'Test School']);
    SchoolStudent::create(['student_id' => $student->id, 'school_id' => $school->id, 'is_current' => true]);
    StudentGuardian::create(['student_id' => $student->id, 'guardian_name' => 'Guardian', 'guardian_phone' => '123', 'is_primary' => true]);

    $attendance = Attendance::create(['student_id' => $student->id, 'date' => '2026-01-15', 'current_in' => false, 'total_time' => 3600]);
    AttendanceAttr::create(['attendance_id' => $attendance->id, 'student_id' => $student->id, 'date' => '2026-01-15', 'time_in' => '08:00', 'time_out' => '09:00']);

    $component = Livewire::actingAs($user)
        ->test(StudentAttendance::class)
        ->set('date', '2026-01-15')
        ->call('getStudentByDate');

    $students = $component->get('students');
    expect($students)->toHaveCount(1);
    expect($students->first()['name'])->toBe('Attending Student');
    expect($students->first()['total_time'])->toBe('01:00:00');
    expect($students->first()['school'])->toBe('Test School');
});

test('getStudentByDate reports the current school, not an old one', function () {
    // Regression test: `school` used to read the SchoolStudent pivot row's `name`
    // attribute (which doesn't exist - name lives on the related School), so this
    // column always fell back to "N/A" regardless of data. It also picked whichever
    // school row came first rather than the one flagged is_current.
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Transferred Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    $oldSchool = School::create(['name' => 'Old School']);
    $newSchool = School::create(['name' => 'New School']);
    SchoolStudent::create(['student_id' => $student->id, 'school_id' => $oldSchool->id, 'is_current' => false]);
    SchoolStudent::create(['student_id' => $student->id, 'school_id' => $newSchool->id, 'is_current' => true]);

    $attendance = Attendance::create(['student_id' => $student->id, 'date' => '2026-01-15', 'current_in' => false, 'total_time' => 600]);
    AttendanceAttr::create(['attendance_id' => $attendance->id, 'student_id' => $student->id, 'date' => '2026-01-15', 'time_in' => '08:00', 'time_out' => '08:10']);

    $component = Livewire::actingAs($user)
        ->test(StudentAttendance::class)
        ->set('date', '2026-01-15')
        ->call('getStudentByDate');

    expect($component->get('students')->first()['school'])->toBe('New School');
});

test('getStudentByDate does not crash when the attending student was soft-deleted', function () {
    // Regression test: $attendance->student is null once the student is
    // soft-deleted (excluded by default), which crashed this report on
    // ->student->id. Same fix as the dashboard's in-session widget.
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Ghost Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    Attendance::create(['student_id' => $student->id, 'date' => '2026-01-15', 'current_in' => false, 'total_time' => 0]);
    $student->delete();

    $component = Livewire::actingAs($user)
        ->test(StudentAttendance::class)
        ->set('date', '2026-01-15')
        ->call('getStudentByDate');

    expect($component->get('students'))->toHaveCount(0);
});

test('getStudentByDate requires a date', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(StudentAttendance::class)
        ->set('date', '')
        ->call('getStudentByDate')
        ->assertHasErrors(['date']);
});

test('mounting the component defaults to today and auto-loads attendance', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Today Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    $attendance = Attendance::create(['student_id' => $student->id, 'date' => now(), 'current_in' => true, 'total_time' => 600]);
    AttendanceAttr::create(['attendance_id' => $attendance->id, 'student_id' => $student->id, 'date' => now(), 'time_in' => '08:00']);

    $component = Livewire::actingAs($user)->test(StudentAttendance::class);

    expect($component->get('date'))->toBe(now()->format('Y-m-d'));
    expect($component->get('students'))->toHaveCount(1);
});

test('the roster table numbers each row', function () {
    $user = User::factory()->create();
    $a = Student::create(['name' => 'A Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    $b = Student::create(['name' => 'B Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    Attendance::create(['student_id' => $a->id, 'date' => '2026-01-15', 'current_in' => true, 'total_time' => 0]);
    Attendance::create(['student_id' => $b->id, 'date' => '2026-01-15', 'current_in' => true, 'total_time' => 0]);

    $html = Livewire::actingAs($user)
        ->test(StudentAttendance::class)
        ->set('date', '2026-01-15')
        ->call('getStudentByDate')
        ->html();

    expect($html)->toContain('<th scope="col" class="px-4 py-3">#</th>');
});

test('the roster is paginated on screen but the print table has every row', function () {
    $user = User::factory()->create();
    collect(range(1, 15))->each(function ($i) {
        $student = Student::create(['name' => sprintf('Student %02d', $i), 'dob' => '2010-01-01', 'gender' => 'male']);
        Attendance::create(['student_id' => $student->id, 'date' => '2026-01-15', 'current_in' => true, 'total_time' => 0]);
    });

    $component = Livewire::actingAs($user)
        ->test(StudentAttendance::class)
        ->set('date', '2026-01-15')
        ->call('getStudentByDate');

    expect($component->get('students'))->toHaveCount(15);
    expect($component->viewData('studentsPage')->count())->toBe(10);
    expect($component->viewData('studentsPage')->total())->toBe(15);

    $html = $component->html();
    expect($html)->toContain('Student 01');
    expect(substr_count($html, 'Student 11'))->toBe(1);
});

test('changing perPage or re-filtering the roster resets back to page 1', function () {
    $user = User::factory()->create();
    collect(range(1, 15))->each(function ($i) {
        $student = Student::create(['name' => sprintf('Student %02d', $i), 'dob' => '2010-01-01', 'gender' => 'male']);
        Attendance::create(['student_id' => $student->id, 'date' => '2026-01-15', 'current_in' => true, 'total_time' => 0]);
    });

    $component = Livewire::actingAs($user)
        ->test(StudentAttendance::class)
        ->set('date', '2026-01-15')
        ->call('getStudentByDate')
        ->call('gotoPage', 2, 'roster-page');

    expect($component->viewData('studentsPage')->currentPage())->toBe(2);

    $component->call('getStudentByDate');
    expect($component->viewData('studentsPage')->currentPage())->toBe(1);
});

test('the gender filter scopes the roster to the selected gender', function () {
    $user = User::factory()->create();
    $boy = Student::create(['name' => 'Boy', 'dob' => '2010-01-01', 'gender' => 'male']);
    $girl = Student::create(['name' => 'Girl', 'dob' => '2010-01-01', 'gender' => 'female']);
    Attendance::create(['student_id' => $boy->id, 'date' => '2026-01-15', 'current_in' => true, 'total_time' => 0]);
    Attendance::create(['student_id' => $girl->id, 'date' => '2026-01-15', 'current_in' => true, 'total_time' => 0]);

    $component = Livewire::actingAs($user)
        ->test(StudentAttendance::class)
        ->set('date', '2026-01-15')
        ->set('gender', 'female')
        ->call('getStudentByDate');

    $students = $component->get('students');
    expect($students)->toHaveCount(1);
    expect($students->first()['name'])->toBe('Girl');
});

test('the school filter scopes the roster to the selected current school', function () {
    $user = User::factory()->create();
    $schoolA = School::create(['name' => 'School A']);
    $schoolB = School::create(['name' => 'School B']);

    $studentA = Student::create(['name' => 'Student A', 'dob' => '2010-01-01', 'gender' => 'male']);
    $studentB = Student::create(['name' => 'Student B', 'dob' => '2010-01-01', 'gender' => 'male']);
    SchoolStudent::create(['student_id' => $studentA->id, 'school_id' => $schoolA->id, 'is_current' => true]);
    SchoolStudent::create(['student_id' => $studentB->id, 'school_id' => $schoolB->id, 'is_current' => true]);

    Attendance::create(['student_id' => $studentA->id, 'date' => '2026-01-15', 'current_in' => true, 'total_time' => 0]);
    Attendance::create(['student_id' => $studentB->id, 'date' => '2026-01-15', 'current_in' => true, 'total_time' => 0]);

    $component = Livewire::actingAs($user)
        ->test(StudentAttendance::class)
        ->set('date', '2026-01-15')
        ->set('schoolId', $schoolA->id)
        ->call('getStudentByDate');

    $students = $component->get('students');
    expect($students)->toHaveCount(1);
    expect($students->first()['name'])->toBe('Student A');
});

test('the grade filter scopes the roster to students currently in that grade', function () {
    $user = User::factory()->create();
    $gradeOne = Grade::create(['grade' => 'Grade 1']);
    $gradeTwo = Grade::create(['grade' => 'Grade 2']);

    $studentA = Student::create(['name' => 'Student A', 'dob' => '2010-01-01', 'gender' => 'male']);
    $studentB = Student::create(['name' => 'Student B', 'dob' => '2010-01-01', 'gender' => 'male']);
    GradeStudent::create(['student_id' => $studentA->id, 'grade' => $gradeOne->id, 'is_current' => true]);
    GradeStudent::create(['student_id' => $studentB->id, 'grade' => $gradeTwo->id, 'is_current' => true]);

    Attendance::create(['student_id' => $studentA->id, 'date' => '2026-01-15', 'current_in' => true, 'total_time' => 0]);
    Attendance::create(['student_id' => $studentB->id, 'date' => '2026-01-15', 'current_in' => true, 'total_time' => 0]);

    $component = Livewire::actingAs($user)
        ->test(StudentAttendance::class)
        ->set('date', '2026-01-15')
        ->set('gradeId', $gradeOne->id)
        ->call('getStudentByDate');

    $students = $component->get('students');
    expect($students)->toHaveCount(1);
    expect($students->first()['name'])->toBe('Student A');
    expect($students->first()['grade'])->toBe('Grade 1');
});

test('guardian contact appears on screen but not on the printed roster', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Rostered Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    StudentGuardian::create([
        'student_id' => $student->id,
        'guardian_name' => 'Mercy Guardian',
        'guardian_phone' => '0700111222',
        'is_primary' => true,
    ]);
    Attendance::create(['student_id' => $student->id, 'date' => '2026-01-15', 'current_in' => true, 'total_time' => 0]);

    $html = Livewire::actingAs($user)
        ->test(StudentAttendance::class)
        ->set('date', '2026-01-15')
        ->call('getStudentByDate')
        ->html();

    // The screen table keeps them so staff can phone a parent.
    expect($html)->toContain('Guardian Phone');
    expect($html)->toContain('0700111222');

    // The print-only block must not repeat them: a printed sheet listing
    // every child's guardian contact is the copy most likely to be mislaid.
    $printBlock = substr($html, strpos($html, 'hidden print:block'));
    expect($printBlock)->not->toContain('0700111222');
    expect($printBlock)->not->toContain('Mercy Guardian');
    // The rest of the printed roster still works.
    expect($printBlock)->toContain('Rostered Student');
});
