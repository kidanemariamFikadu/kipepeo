<?php

use App\Livewire\StudentList;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

test('the primary guardian is shown when a student has more than one guardian', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Test Student', 'dob' => '2015-01-01', 'gender' => 'male']);
    StudentGuardian::create(['student_id' => $student->id, 'guardian_name' => 'Secondary Guardian', 'guardian_phone' => '111', 'is_primary' => false]);
    StudentGuardian::create(['student_id' => $student->id, 'guardian_name' => 'Primary Guardian', 'guardian_phone' => '222', 'is_primary' => true]);

    $html = Livewire::actingAs($user)->test(StudentList::class)->html();

    expect($html)->toContain('Primary Guardian - 222');
    expect($html)->toContain('+1 more');
});

test('the student list does not N+1 query guardians per row', function () {
    $user = User::factory()->create();
    foreach (range(1, 5) as $i) {
        $student = Student::create(['name' => "Student $i", 'dob' => '2015-01-01', 'gender' => 'male']);
        StudentGuardian::create(['student_id' => $student->id, 'guardian_name' => "Guardian $i", 'guardian_phone' => '000', 'is_primary' => true]);
    }

    $queryCount = 0;
    DB::listen(function () use (&$queryCount) {
        $queryCount++;
    });

    Livewire::actingAs($user)->test(StudentList::class);

    expect($queryCount)->toBeLessThan(10);
});

test('non-admin does not see selection checkboxes or the delete button', function () {
    $user = User::factory()->create(['role' => 'user']);
    Student::create(['name' => 'Test Student', 'dob' => '2015-01-01', 'gender' => 'male']);

    $html = Livewire::actingAs($user)->test(StudentList::class)->html();

    expect($html)->not->toContain('wire:model="selectedStudents"');
    expect($html)->not->toContain('deleteRecord(');
});

test('graduated students are excluded from the student list', function () {
    $user = User::factory()->create();
    Student::create(['name' => 'Active Student', 'dob' => '2015-01-01', 'gender' => 'male']);
    Student::create(['name' => 'Graduated Student', 'dob' => '2010-01-01', 'gender' => 'male', 'graduated_at' => now()]);

    $html = Livewire::actingAs($user)->test(StudentList::class)->html();

    expect($html)->toContain('Active Student');
    expect($html)->not->toContain('Graduated Student');
});

test('toggleSelectAll does not select graduated students', function () {
    $user = User::factory()->create();
    $active = Student::create(['name' => 'Active Student', 'dob' => '2015-01-01', 'gender' => 'male']);
    Student::create(['name' => 'Graduated Student', 'dob' => '2010-01-01', 'gender' => 'male', 'graduated_at' => now()]);

    $component = Livewire::actingAs($user)->test(StudentList::class)->call('toggleSelectAll');

    expect($component->get('selectedStudents'))->toBe([$active->id]);
});
