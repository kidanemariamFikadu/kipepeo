<?php

use App\Livewire\Setting\Index;
use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

it('promotes students with a current grade to the next grade', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $gradeOne = Grade::create(['grade' => 'GRADE 1']);
    $gradeTwo = Grade::create(['grade' => 'GRADE 2']);

    $student = Student::create([
        'name' => 'Jane Student',
        'dob' => '2014-01-01',
        'gender' => 'female',
    ]);

    GradeStudent::create([
        'student_id' => $student->id,
        'grade' => $gradeOne->id,
        'is_current' => true,
    ]);

    Livewire::test(Index::class)
        ->call('promoteStudentsToNextGrade');

    expect(GradeStudent::where([
        'student_id' => $student->id,
        'grade' => (string) $gradeOne->id,
    ])->first()->is_current)->toBeFalse();

    expect(GradeStudent::where([
        'student_id' => $student->id,
        'grade' => (string) $gradeTwo->id,
    ])->first()->is_current)->toBeTrue();
});

it('keeps students at the final grade without changing current grade', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $finalGrade = Grade::create(['grade' => 'GRADE 12']);
    $student = Student::create([
        'name' => 'John Final',
        'dob' => '2010-01-01',
        'gender' => 'male',
    ]);

    GradeStudent::create([
        'student_id' => $student->id,
        'grade' => $finalGrade->id,
        'is_current' => true,
    ]);

    Livewire::test(Index::class)
        ->call('promoteStudentsToNextGrade');

    expect(GradeStudent::where([
        'student_id' => $student->id,
        'grade' => (string) $finalGrade->id,
    ])->first()->is_current)->toBeTrue();
});
