<?php

use App\Livewire\Student\CreateStudent;
use App\Models\Grade;
use App\Models\School;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

function fillCreateStudentForm($component, School $school, Grade $grade, string $dob)
{
    return $component
        ->set('form.name', 'New Student')
        ->set('form.gender', 'male')
        ->set('form.dob', $dob)
        ->set('form.school', $school->id)
        ->set('form.grade', $grade->id)
        ->set('form.guardian_name', 'Guardian Name')
        ->set('form.guardian_phone', '0712345678');
}

test('creating a student outside data entry mode allows a child under 5 (e.g. born after 2020)', function () {
    // Regression test: a leftover minimum-age check (misleadingly named
    // $date18YearsAgo but actually enforcing 5+ years) blocked entering any
    // child born in the last ~5 years, even though the school's own grade
    // list includes pre-school grades like "NOT YET IN SCHOOL" and "PLAYGROUP".
    $user = User::factory()->create();
    $school = School::create(['name' => 'Test School']);
    $grade = Grade::create(['grade' => 'PLAYGROUP']);

    fillCreateStudentForm(
        Livewire::actingAs($user)->test(CreateStudent::class),
        $school,
        $grade,
        now()->subYears(2)->format('Y-m-d')
    )->call('create')->assertDispatched('student-changed')->assertDispatched('dashboard-changed');

    expect(Student::where('name', 'New Student')->exists())->toBeTrue();
});

test('creating a student outside data entry mode rejects a date of birth in the future', function () {
    $user = User::factory()->create();
    $school = School::create(['name' => 'Test School']);
    $grade = Grade::create(['grade' => 'GRADE 1']);

    fillCreateStudentForm(
        Livewire::actingAs($user)->test(CreateStudent::class),
        $school,
        $grade,
        now()->addDays(1)->format('Y-m-d')
    )->call('create')->assertHasErrors(['form.dob']);

    expect(Student::count())->toBe(0);
});

test('creating a student outside data entry mode succeeds for an eligible student', function () {
    $user = User::factory()->create();
    $school = School::create(['name' => 'Test School']);
    $grade = Grade::create(['grade' => 'GRADE 1']);

    fillCreateStudentForm(
        Livewire::actingAs($user)->test(CreateStudent::class),
        $school,
        $grade,
        now()->subYears(10)->format('Y-m-d')
    )->call('create')->assertDispatched('student-changed')->assertDispatched('dashboard-changed');

    expect(Student::where('name', 'New Student')->exists())->toBeTrue();
});

test('creating a student in data entry mode does not require a dob', function () {
    $user = User::factory()->create();
    $school = School::create(['name' => 'Test School']);
    $grade = Grade::create(['grade' => 'GRADE 1']);

    Livewire::actingAs($user)
        ->test(CreateStudent::class, ['isDataEntry' => true])
        ->set('form.name', 'DataEntry Student')
        ->set('form.gender', 'male')
        ->set('form.school', $school->id)
        ->set('form.grade', $grade->id)
        ->set('form.guardian_name', 'Guardian Name')
        ->set('form.guardian_phone', '0712345678')
        ->call('create')
        ->assertDispatched('student-changed');

    expect(Student::where('name', 'DataEntry Student')->exists())->toBeTrue();
});
