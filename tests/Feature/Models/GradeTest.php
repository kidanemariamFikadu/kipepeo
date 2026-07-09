<?php

use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\Student;

test('nextGrade resolves the configured progression', function () {
    $grade1 = Grade::create(['grade' => 'GRADE 1']);
    $grade2 = Grade::create(['grade' => 'GRADE 2']);
    $grade1->update(['next_grade_id' => $grade2->id]);

    expect($grade1->fresh()->nextGrade->id)->toBe($grade2->id);
});

test('nextGrade is null for a grade with no progression configured', function () {
    $grade = Grade::create(['grade' => 'GRADE 12']);

    expect($grade->nextGrade)->toBeNull();
});

test('deleting the target of a next grade nulls out the reference instead of failing', function () {
    $grade1 = Grade::create(['grade' => 'GRADE 1']);
    $grade2 = Grade::create(['grade' => 'GRADE 2']);
    $grade1->update(['next_grade_id' => $grade2->id]);

    $grade2->forceDelete();

    expect($grade1->fresh()->next_grade_id)->toBeNull();
});

test('gradeStudents returns the pivot rows for this grade', function () {
    $grade = Grade::create(['grade' => 'GRADE 1']);
    $student = Student::create(['name' => 'Test', 'dob' => '2010-01-01', 'gender' => 'male']);
    GradeStudent::create(['student_id' => $student->id, 'grade' => $grade->id, 'is_current' => true]);

    expect($grade->gradeStudents)->toHaveCount(1);
    expect($grade->gradeStudents->first()->student_id)->toBe($student->id);
});
