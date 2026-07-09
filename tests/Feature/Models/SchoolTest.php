<?php

use App\Models\School;
use App\Models\SchoolStudent;
use App\Models\Student;

test('students relationship returns students linked through school_students', function () {
    $school = School::create(['name' => 'Green Valley']);
    $studentA = Student::create(['name' => 'A', 'dob' => '2010-01-01', 'gender' => 'male']);
    $studentB = Student::create(['name' => 'B', 'dob' => '2011-01-01', 'gender' => 'female']);
    $unrelated = Student::create(['name' => 'C', 'dob' => '2012-01-01', 'gender' => 'male']);

    SchoolStudent::create(['school_id' => $school->id, 'student_id' => $studentA->id, 'is_current' => true]);
    SchoolStudent::create(['school_id' => $school->id, 'student_id' => $studentB->id, 'is_current' => false]);

    $students = $school->students;

    expect($students)->toHaveCount(2);
    expect($students->pluck('id'))->not->toContain($unrelated->id);
});

test('search scope matches by name', function () {
    School::create(['name' => 'Riverside Academy']);
    School::create(['name' => 'Lakeside School']);

    $results = School::search('River')->get();

    expect($results)->toHaveCount(1);
    expect($results->first()->name)->toBe('Riverside Academy');
});
