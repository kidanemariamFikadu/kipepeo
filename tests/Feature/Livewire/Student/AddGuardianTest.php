<?php

use App\Livewire\Student\AddGuardian;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\User;
use Livewire\Livewire;

test('creating a guardian marked primary unsets the previous primary guardian', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Test', 'dob' => '2010-01-01', 'gender' => 'male']);
    $existingPrimary = StudentGuardian::create(['student_id' => $student->id, 'guardian_name' => 'Old Primary', 'guardian_phone' => '0700000000', 'is_primary' => true]);

    Livewire::actingAs($user)
        ->test(AddGuardian::class, ['studentId' => $student->id])
        ->set('addStudentGuardianForm.guardian_name', 'New Guardian')
        ->set('addStudentGuardianForm.guardian_phone', '0712345678')
        ->set('addStudentGuardianForm.is_primary', true)
        ->call('createGuardian')
        ->assertDispatched('student-changed');

    expect($existingPrimary->fresh()->is_primary)->toBeFalsy();
    $newGuardian = StudentGuardian::where('guardian_name', 'New Guardian')->first();
    expect($newGuardian->is_primary)->toBeTruthy();
});

test('the modal title reflects add vs edit mode', function () {
    // Regression test: the header used to hardcode "Add Student" regardless
    // of context - copy-pasted from the create-student modal and never updated.
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Test', 'dob' => '2010-01-01', 'gender' => 'male']);
    $guardian = StudentGuardian::create(['student_id' => $student->id, 'guardian_name' => 'Existing', 'guardian_phone' => '0700000000', 'is_primary' => false]);

    $addHtml = Livewire::actingAs($user)->test(AddGuardian::class, ['studentId' => $student->id])->html();
    expect($addHtml)->toContain('Add Guardian');
    expect($addHtml)->not->toContain('Add Student');

    $editHtml = Livewire::actingAs($user)->test(AddGuardian::class, ['studentGuardian' => $guardian])->html();
    expect($editHtml)->toContain('Edit Guardian');
});

test('editing an existing guardian updates its fields', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Test', 'dob' => '2010-01-01', 'gender' => 'male']);
    $guardian = StudentGuardian::create(['student_id' => $student->id, 'guardian_name' => 'Original Name', 'guardian_phone' => '0700000000', 'is_primary' => false]);

    Livewire::actingAs($user)
        ->test(AddGuardian::class, ['studentGuardian' => $guardian])
        ->set('addStudentGuardianForm.guardian_name', 'Updated Name')
        ->call('createGuardian')
        ->assertDispatched('student-changed');

    expect($guardian->fresh()->guardian_name)->toBe('Updated Name');
});
