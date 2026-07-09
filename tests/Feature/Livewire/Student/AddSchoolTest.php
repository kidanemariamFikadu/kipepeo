<?php

use App\Livewire\Student\AddSchool;
use App\Models\School;
use App\Models\SchoolStudent;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

test('adding a school makes it the current school and demotes the previous current school', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Test', 'dob' => '2010-01-01', 'gender' => 'male']);
    $oldSchool = School::create(['name' => 'Old School']);
    $newSchool = School::create(['name' => 'New School']);
    SchoolStudent::create(['student_id' => $student->id, 'school_id' => $oldSchool->id, 'is_current' => true]);

    Livewire::actingAs($user)
        ->test(AddSchool::class, ['studentId' => $student->id])
        ->set('school_id', $newSchool->id)
        ->call('createSchool')
        ->assertDispatched('student-changed');

    expect(SchoolStudent::where(['student_id' => $student->id, 'school_id' => $oldSchool->id])->first()->is_current)->toBeFalsy();
    expect(SchoolStudent::where(['student_id' => $student->id, 'school_id' => $newSchool->id])->first()->is_current)->toBeTruthy();
});

test('adding a duplicate school flashes an error instead of creating a new row', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Test', 'dob' => '2010-01-01', 'gender' => 'male']);
    $school = School::create(['name' => 'Existing School']);
    SchoolStudent::create(['student_id' => $student->id, 'school_id' => $school->id, 'is_current' => true]);

    Livewire::actingAs($user)
        ->test(AddSchool::class, ['studentId' => $student->id])
        ->set('school_id', $school->id)
        ->call('createSchool');

    expect(SchoolStudent::where(['student_id' => $student->id, 'school_id' => $school->id])->count())->toBe(1);
});
