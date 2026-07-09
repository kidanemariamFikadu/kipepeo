<?php

use App\Models\Attendance;
use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\School;
use App\Models\SchoolStudent;
use App\Models\Student;
use Carbon\Carbon;

test('currentSchool and currentGrade accessors return only the row marked is_current', function () {
    $student = Student::create(['name' => 'Test', 'dob' => '2010-01-01', 'gender' => 'male']);

    $oldSchool = School::create(['name' => 'Old School']);
    $currentSchool = School::create(['name' => 'Current School']);
    SchoolStudent::create(['student_id' => $student->id, 'school_id' => $oldSchool->id, 'is_current' => false]);
    SchoolStudent::create(['student_id' => $student->id, 'school_id' => $currentSchool->id, 'is_current' => true]);

    $oldGrade = Grade::create(['grade' => 'GRADE 1']);
    $currentGrade = Grade::create(['grade' => 'GRADE 2']);
    GradeStudent::create(['student_id' => $student->id, 'grade' => $oldGrade->id, 'is_current' => false]);
    GradeStudent::create(['student_id' => $student->id, 'grade' => $currentGrade->id, 'is_current' => true]);

    expect($student->currentSchool->id)->toBe($currentSchool->id);
    expect($student->currentGrade->id)->toBe($currentGrade->id);
});

test('currentSchool and currentGrade return null when no row is marked current', function () {
    $student = Student::create(['name' => 'Test', 'dob' => '2010-01-01', 'gender' => 'male']);

    expect($student->currentSchool)->toBeNull();
    expect($student->currentGrade)->toBeNull();
});

test('studentAge accessor derives age from dob', function () {
    $dob = Carbon::now()->subYears(10)->format('Y-m-d');
    $student = Student::create(['name' => 'Test', 'dob' => $dob, 'gender' => 'male']);

    expect($student->studentAge)->toBe(10);
});

test('currentAttendance accessor reflects whether the student is currently checked in today', function () {
    $student = Student::create(['name' => 'Test', 'dob' => '2010-01-01', 'gender' => 'male']);

    Attendance::create([
        'student_id' => $student->id,
        'date' => now(),
        'current_in' => true,
        'total_time' => 0,
    ]);

    expect($student->currentAttendance)->toBeTruthy();
});

test('todayTotalTime accessor formats seconds as h:m:s', function () {
    $student = Student::create(['name' => 'Test', 'dob' => '2010-01-01', 'gender' => 'male']);

    Attendance::create([
        'student_id' => $student->id,
        'date' => now(),
        'current_in' => false,
        'total_time' => 3661,
    ]);

    expect($student->todayTotalTime)->toBe('01:01:01');
});

test('search scope matches by name', function () {
    Student::create(['name' => 'Alpha Zebra', 'dob' => '2010-01-01', 'gender' => 'male']);
    Student::create(['name' => 'Beta', 'dob' => '2010-01-01', 'gender' => 'male']);

    $results = Student::search('Zebra')->get();

    expect($results)->toHaveCount(1);
});
