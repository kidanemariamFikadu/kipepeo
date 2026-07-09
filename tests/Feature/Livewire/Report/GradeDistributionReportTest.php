<?php

use App\Livewire\Report\GradeDistributionReport;
use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\School;
use App\Models\SchoolStudent;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

test('grade distribution counts currently-assigned students per grade by gender', function () {
    $user = User::factory()->create();
    $grade = Grade::create(['grade' => 'GRADE 1']);

    $male = Student::create(['name' => 'Male Student', 'dob' => '2010-01-01', 'gender' => 'Male']);
    $female = Student::create(['name' => 'Female Student', 'dob' => '2010-01-01', 'gender' => 'Female']);
    $old = Student::create(['name' => 'Old Grade Student', 'dob' => '2010-01-01', 'gender' => 'Male']);

    GradeStudent::create(['student_id' => $male->id, 'grade' => $grade->id, 'is_current' => true]);
    GradeStudent::create(['student_id' => $female->id, 'grade' => $grade->id, 'is_current' => true]);
    GradeStudent::create(['student_id' => $old->id, 'grade' => $grade->id, 'is_current' => false]);

    $report = Livewire::actingAs($user)->test(GradeDistributionReport::class)
        ->viewData('grades')->firstWhere('id', $grade->id);

    expect($report->total_students)->toBe(2);
    expect($report->male_students_count)->toBe(1);
    expect($report->female_students_count)->toBe(1);
});

test('grade distribution counts currently-enrolled students who have no current grade assigned', function () {
    $user = User::factory()->create();
    $school = School::create(['name' => 'Test School']);
    $grade = Grade::create(['grade' => 'GRADE 1']);

    $assigned = Student::create(['name' => 'Assigned', 'dob' => '2010-01-01', 'gender' => 'Male']);
    $unassigned = Student::create(['name' => 'Unassigned', 'dob' => '2010-01-01', 'gender' => 'Male']);

    SchoolStudent::create(['student_id' => $assigned->id, 'school_id' => $school->id, 'is_current' => true]);
    SchoolStudent::create(['student_id' => $unassigned->id, 'school_id' => $school->id, 'is_current' => true]);
    GradeStudent::create(['student_id' => $assigned->id, 'grade' => $grade->id, 'is_current' => true]);

    $component = Livewire::actingAs($user)->test(GradeDistributionReport::class);

    expect($component->viewData('totalEnrolled'))->toBe(2);
    expect($component->viewData('unassignedCount'))->toBe(1);
});
