<?php

use App\Livewire\Setting\School as SchoolComponent;
use App\Livewire\Setting\SchoolList;
use App\Models\School;
use App\Models\SchoolStudent;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

test('creating a school persists it', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(SchoolComponent::class)
        ->set('school', 'Brand New School')
        ->call('createSchool')
        ->assertDispatched('school-changed');

    expect(School::where('name', 'Brand New School')->exists())->toBeTrue();
});

test('creating a duplicate school name flashes an error', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    School::create(['name' => 'Existing School']);

    Livewire::actingAs($admin)
        ->test(SchoolComponent::class)
        ->set('school', 'Existing School')
        ->call('createSchool')
        ->assertHasErrors(['school']);
});

test('editing a school without changing its name fails the unique validation rule', function () {
    // The #[Validate] unique rule on `school` doesn't exclude the record being edited,
    // so re-submitting the form unchanged always fails validation before the
    // "compare against other schools" duplicate check further down is ever reached.
    $admin = User::factory()->create(['role' => 'admin']);
    $schoolToEdit = School::create(['name' => 'School A']);

    Livewire::actingAs($admin)
        ->test(SchoolComponent::class, ['schoolId' => $schoolToEdit->id])
        ->set('school', 'School A')
        ->call('createSchool')
        ->assertHasErrors(['school']);
});

test('editing a school to a genuinely new name updates it', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $schoolToEdit = School::create(['name' => 'School A']);

    Livewire::actingAs($admin)
        ->test(SchoolComponent::class, ['schoolId' => $schoolToEdit->id])
        ->set('school', 'Renamed School')
        ->call('createSchool')
        ->assertDispatched('school-changed');

    expect($schoolToEdit->fresh()->name)->toBe('Renamed School');
});

test('removeSchool refuses to delete a school with associated students', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $school = School::create(['name' => 'Populated School']);
    $student = Student::create(['name' => 'Test', 'dob' => '2010-01-01', 'gender' => 'male']);
    SchoolStudent::create(['school_id' => $school->id, 'student_id' => $student->id, 'is_current' => true]);

    Livewire::actingAs($admin)
        ->test(SchoolList::class)
        ->call('removeSchool', $school->id)
        ->assertDispatched('MessageChanged');

    expect(School::find($school->id))->not->toBeNull();
});

test('removeSchool deletes a school with no associated students', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $school = School::create(['name' => 'Empty School']);

    Livewire::actingAs($admin)
        ->test(SchoolList::class)
        ->call('removeSchool', $school->id);

    expect(School::find($school->id))->toBeNull();
});
