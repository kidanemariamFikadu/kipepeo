<?php

use App\Livewire\Report\AlumniReport;
use App\Models\Grade;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

test('alumni report lists only graduated students', function () {
    $user = User::factory()->create();
    $grade = Grade::create(['grade' => 'GRADE 12']);

    $graduated = Student::create(['name' => 'Graduate', 'dob' => '2005-01-01', 'gender' => 'male', 'graduated_at' => now(), 'graduated_grade_id' => $grade->id]);
    Student::create(['name' => 'Active', 'dob' => '2015-01-01', 'gender' => 'male']);

    $component = Livewire::actingAs($user)->test(AlumniReport::class);

    expect($component->viewData('alumni')->pluck('id')->all())->toBe([$graduated->id]);
    expect($component->viewData('totalAlumni'))->toBe(1);
});

test('alumni report filters by graduation date range', function () {
    $user = User::factory()->create();
    $grade = Grade::create(['grade' => 'GRADE 12']);

    $recent = Student::create(['name' => 'Recent Graduate', 'dob' => '2005-01-01', 'gender' => 'male', 'graduated_at' => now(), 'graduated_grade_id' => $grade->id]);
    Student::create(['name' => 'Old Graduate', 'dob' => '2000-01-01', 'gender' => 'male', 'graduated_at' => now()->subYears(3), 'graduated_grade_id' => $grade->id]);

    $component = Livewire::actingAs($user)->test(AlumniReport::class)
        ->set('fromDate', now()->subDay()->format('Y-m-d'))
        ->call('filter');

    expect($component->viewData('alumni')->pluck('id')->all())->toBe([$recent->id]);
});

test('alumni report filters by graduated-from grade', function () {
    $user = User::factory()->create();
    $gradeA = Grade::create(['grade' => 'GRADE 12']);
    $gradeB = Grade::create(['grade' => 'FORM 4']);

    $fromA = Student::create(['name' => 'From A', 'dob' => '2005-01-01', 'gender' => 'male', 'graduated_at' => now(), 'graduated_grade_id' => $gradeA->id]);
    Student::create(['name' => 'From B', 'dob' => '2005-01-01', 'gender' => 'male', 'graduated_at' => now(), 'graduated_grade_id' => $gradeB->id]);

    $component = Livewire::actingAs($user)->test(AlumniReport::class)
        ->set('gradeId', $gradeA->id)
        ->call('filter');

    expect($component->viewData('alumni')->pluck('id')->all())->toBe([$fromA->id]);
});

test('alumni report validates toDate is not before fromDate', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(AlumniReport::class)
        ->set('fromDate', now()->format('Y-m-d'))
        ->set('toDate', now()->subDay()->format('Y-m-d'))
        ->call('filter')
        ->assertHasErrors(['toDate']);
});
