<?php

use App\Livewire\Setting\Grade as GradeComponent;
use App\Livewire\Setting\GradeList;
use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

test('creating a grade persists it', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(GradeComponent::class)
        ->set('grade', 'GRADE 5')
        ->call('createGrade')
        ->assertDispatched('grade-changed');

    expect(Grade::where('grade', 'GRADE 5')->exists())->toBeTrue();
});

test('creating a duplicate grade fails the unique validation rule', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Grade::create(['grade' => 'GRADE 1']);

    Livewire::actingAs($admin)
        ->test(GradeComponent::class)
        ->set('grade', 'GRADE 1')
        ->call('createGrade')
        ->assertHasErrors(['grade']);

    expect(Grade::where('grade', 'GRADE 1')->count())->toBe(1);
});

test('editing a grade without changing its name succeeds', function () {
    // Regression test: the unique rule used to run without excluding the record
    // being edited, so re-saving a grade unchanged always failed validation.
    $admin = User::factory()->create(['role' => 'admin']);
    $grade = Grade::create(['grade' => 'GRADE 1']);

    Livewire::actingAs($admin)
        ->test(GradeComponent::class, ['gradeId' => $grade->id])
        ->set('grade', 'GRADE 1')
        ->call('createGrade')
        ->assertHasNoErrors();

    expect($grade->fresh()->grade)->toBe('GRADE 1');
});

test('creating a grade with a next grade sets the progression', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $nextGrade = Grade::create(['grade' => 'GRADE 2']);

    Livewire::actingAs($admin)
        ->test(GradeComponent::class)
        ->set('grade', 'GRADE 1')
        ->set('nextGradeId', $nextGrade->id)
        ->call('createGrade');

    expect(Grade::where('grade', 'GRADE 1')->first()->next_grade_id)->toBe($nextGrade->id);
});

test('editing a grade can change its next grade', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $grade = Grade::create(['grade' => 'GRADE 1']);
    $oldNext = Grade::create(['grade' => 'GRADE 2']);
    $newNext = Grade::create(['grade' => 'GRADE 3']);
    $grade->update(['next_grade_id' => $oldNext->id]);

    Livewire::actingAs($admin)
        ->test(GradeComponent::class, ['gradeId' => $grade->id])
        ->set('grade', 'GRADE 1')
        ->set('nextGradeId', $newNext->id)
        ->call('createGrade');

    expect($grade->fresh()->next_grade_id)->toBe($newNext->id);
});

test('the next grade dropdown never offers the grade being edited as its own next grade', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $grade = Grade::create(['grade' => 'GRADE 1']);

    $component = Livewire::actingAs($admin)->test(GradeComponent::class, ['gradeId' => $grade->id]);

    expect($component->get('otherGrades')->pluck('id'))->not->toContain($grade->id);
});

test('removeGrade refuses to delete a grade with associated students', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $grade = Grade::create(['grade' => 'Populated Grade']);
    $student = Student::create(['name' => 'Test', 'dob' => '2010-01-01', 'gender' => 'male']);
    GradeStudent::create(['grade' => $grade->id, 'student_id' => $student->id, 'is_current' => true]);

    Livewire::actingAs($admin)
        ->test(GradeList::class)
        ->call('removeGrade', $grade->id)
        ->assertDispatched('MessageChanged');

    expect(Grade::find($grade->id))->not->toBeNull();
});

test('removeGrade deletes a grade with no associated students', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $grade = Grade::create(['grade' => 'Empty Grade']);

    Livewire::actingAs($admin)
        ->test(GradeList::class)
        ->call('removeGrade', $grade->id);

    expect(Grade::find($grade->id))->toBeNull();
});
