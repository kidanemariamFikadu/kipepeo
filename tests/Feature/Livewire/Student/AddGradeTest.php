<?php

use App\Livewire\Student\AddGrade;
use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

test('adding a grade makes it the current grade and demotes the previous current grade', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Test', 'dob' => '2010-01-01', 'gender' => 'male']);
    $oldGrade = Grade::create(['grade' => 'GRADE 1']);
    $newGrade = Grade::create(['grade' => 'GRADE 2']);
    GradeStudent::create(['student_id' => $student->id, 'grade' => $oldGrade->id, 'is_current' => true]);

    Livewire::actingAs($user)
        ->test(AddGrade::class, ['studentId' => $student->id])
        ->set('grade', $newGrade->id)
        ->call('createGrade')
        ->assertDispatched('student-changed');

    expect(GradeStudent::where(['student_id' => $student->id, 'grade' => $oldGrade->id])->first()->is_current)->toBeFalsy();
    expect(GradeStudent::where(['student_id' => $student->id, 'grade' => $newGrade->id])->first()->is_current)->toBeTruthy();
});

test('adding a duplicate grade flashes an error instead of creating a new row', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Test', 'dob' => '2010-01-01', 'gender' => 'male']);
    $grade = Grade::create(['grade' => 'GRADE 1']);
    GradeStudent::create(['student_id' => $student->id, 'grade' => $grade->id, 'is_current' => true]);

    Livewire::actingAs($user)
        ->test(AddGrade::class, ['studentId' => $student->id])
        ->set('grade', $grade->id)
        ->call('createGrade');

    expect(GradeStudent::where(['student_id' => $student->id, 'grade' => $grade->id])->count())->toBe(1);
});
