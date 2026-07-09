<?php

use App\Livewire\Report\StudentReport;
use App\Models\School;
use App\Models\SchoolStudent;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

test('school report counts students by gender for each school', function () {
    $user = User::factory()->create();
    $school = School::create(['name' => 'Report School']);

    $male = Student::create(['name' => 'Male Student', 'dob' => '2010-01-01', 'gender' => 'Male']);
    $female = Student::create(['name' => 'Female Student', 'dob' => '2010-01-01', 'gender' => 'Female']);
    SchoolStudent::create(['student_id' => $male->id, 'school_id' => $school->id, 'is_current' => true]);
    SchoolStudent::create(['student_id' => $female->id, 'school_id' => $school->id, 'is_current' => true]);

    $component = Livewire::actingAs($user)->test(StudentReport::class);
    $report = $component->viewData('schoolReport')->firstWhere('id', $school->id);

    expect($report->total_students)->toBe(2);
    expect($report->male_students_count)->toBe(1);
    expect($report->female_students_count)->toBe(1);
});

test('search filters the school report by school name', function () {
    $user = User::factory()->create();
    School::create(['name' => 'Zebra School']);
    School::create(['name' => 'Other School']);

    $component = Livewire::actingAs($user)
        ->test(StudentReport::class)
        ->set('search', 'Zebra');

    expect($component->viewData('schoolReport'))->toHaveCount(1);
});

test('the full report used for printing includes every matching school, not just the current page', function () {
    // Regression test: printing used to only show whatever page the paginator was
    // on. The print view now renders a separate, unpaginated query.
    $user = User::factory()->create();
    School::create(['name' => 'School A']);
    School::create(['name' => 'School B']);
    School::create(['name' => 'School C']);

    $component = Livewire::actingAs($user)
        ->test(StudentReport::class)
        ->set('perPage', 2);

    expect($component->viewData('schoolReport'))->toHaveCount(2);
    expect($component->viewData('fullSchoolReport'))->toHaveCount(3);
});
