<?php

use App\Livewire\Setting\PromoteStudents;
use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

function makeStudentInGrade(Grade $grade): Student
{
    $student = Student::create(['name' => 'Student '.uniqid(), 'dob' => '2015-01-01', 'gender' => 'male']);
    GradeStudent::create(['student_id' => $student->id, 'grade' => $grade->id, 'is_current' => true]);

    return $student;
}

test('promote moves every current student in a selected grade to its configured next grade', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $grade1 = Grade::create(['grade' => 'GRADE 1']);
    $grade2 = Grade::create(['grade' => 'GRADE 2']);
    $grade1->update(['next_grade_id' => $grade2->id]);

    $studentA = makeStudentInGrade($grade1);
    $studentB = makeStudentInGrade($grade1);

    Livewire::actingAs($admin)
        ->test(PromoteStudents::class)
        ->set('selectedGrades', [$grade1->id])
        ->call('promote');

    foreach ([$studentA, $studentB] as $student) {
        $current = GradeStudent::where('student_id', $student->id)->where('is_current', true)->first();
        expect((int) $current->grade)->toBe($grade2->id);

        $old = GradeStudent::where('student_id', $student->id)->where('grade', $grade1->id)->first();
        expect($old->is_current)->toBeFalsy();
    }
});

test('promote preserves grade history instead of deleting the old record', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $grade1 = Grade::create(['grade' => 'GRADE 1']);
    $grade2 = Grade::create(['grade' => 'GRADE 2']);
    $grade1->update(['next_grade_id' => $grade2->id]);
    $student = makeStudentInGrade($grade1);

    Livewire::actingAs($admin)
        ->test(PromoteStudents::class)
        ->set('selectedGrades', [$grade1->id])
        ->call('promote');

    expect(GradeStudent::where('student_id', $student->id)->count())->toBe(2);
});

test('promote skips a grade with no next grade configured and reports it', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $terminalGrade = Grade::create(['grade' => 'GRADE 12']);
    $student = makeStudentInGrade($terminalGrade);

    Livewire::actingAs($admin)
        ->test(PromoteStudents::class)
        ->set('selectedGrades', [$terminalGrade->id])
        ->call('promote');

    $current = GradeStudent::where('student_id', $student->id)->where('is_current', true)->first();
    expect((int) $current->grade)->toBe($terminalGrade->id);
    expect(GradeStudent::where('student_id', $student->id)->count())->toBe(1);
});

test('promote requires at least one grade to be selected', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(PromoteStudents::class)
        ->call('promote')
        ->assertHasErrors(['selectedGrades']);
});

test('non-admin cannot promote students', function () {
    $user = User::factory()->create(['role' => 'user']);
    $grade1 = Grade::create(['grade' => 'GRADE 1']);
    $grade2 = Grade::create(['grade' => 'GRADE 2']);
    $grade1->update(['next_grade_id' => $grade2->id]);
    $student = makeStudentInGrade($grade1);

    Livewire::actingAs($user)
        ->test(PromoteStudents::class)
        ->set('selectedGrades', [$grade1->id])
        ->call('promote')
        ->assertForbidden();

    expect((int) GradeStudent::where('student_id', $student->id)->where('is_current', true)->first()->grade)->toBe($grade1->id);
});

test('gradeSummary only lists grades that currently have students', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $populated = Grade::create(['grade' => 'GRADE 1']);
    Grade::create(['grade' => 'GRADE 2']); // empty, should not appear
    makeStudentInGrade($populated);

    $component = Livewire::actingAs($admin)->test(PromoteStudents::class);

    $summary = $component->get('gradeSummary');
    expect($summary->pluck('grade')->all())->toBe(['GRADE 1']);
    expect($summary->first()->current_students_count)->toBe(1);
});

test('toggleSelectAll only selects grades that have a next grade configured', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $grade1 = Grade::create(['grade' => 'GRADE 1']);
    $grade2 = Grade::create(['grade' => 'GRADE 2']);
    $grade1->update(['next_grade_id' => $grade2->id]);
    $terminal = Grade::create(['grade' => 'GRADE 12']);
    makeStudentInGrade($grade1);
    makeStudentInGrade($terminal);

    Livewire::actingAs($admin)
        ->test(PromoteStudents::class)
        ->call('toggleSelectAll', true)
        ->assertSet('selectedGrades', [$grade1->id]);
});

test('a soft-deleted student is not promoted', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $grade1 = Grade::create(['grade' => 'GRADE 1']);
    $grade2 = Grade::create(['grade' => 'GRADE 2']);
    $grade1->update(['next_grade_id' => $grade2->id]);
    $student = makeStudentInGrade($grade1);
    $student->delete();

    Livewire::actingAs($admin)
        ->test(PromoteStudents::class)
        ->set('selectedGrades', [$grade1->id])
        ->call('promote');

    expect(GradeStudent::where('student_id', $student->id)->count())->toBe(1);
});
