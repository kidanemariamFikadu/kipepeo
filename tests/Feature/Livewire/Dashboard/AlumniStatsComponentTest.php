<?php

use App\Livewire\Dashboard\AlumniStatsComponent;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

test('alumni stats component counts total alumni and alumni graduated this year', function () {
    $user = User::factory()->create();

    Student::create(['name' => 'This Year Grad', 'dob' => '2005-01-01', 'gender' => 'male', 'graduated_at' => now()]);
    Student::create(['name' => 'Old Grad', 'dob' => '2000-01-01', 'gender' => 'female', 'graduated_at' => now()->subYears(3)]);
    Student::create(['name' => 'Active Student', 'dob' => '2015-01-01', 'gender' => 'male']);

    $component = Livewire::actingAs($user)->test(AlumniStatsComponent::class);

    expect($component->viewData('totalAlumni'))->toBe(2);
    expect($component->viewData('graduatedThisYear'))->toBe(1);
});
