<?php

use App\Livewire\Setting\PromoteStudents;
use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\School;
use App\Models\SchoolStudent;
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

test('promoting a grade with no next grade configured graduates its students instead', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $terminalGrade = Grade::create(['grade' => 'GRADE 12']);
    $student = makeStudentInGrade($terminalGrade);

    Livewire::actingAs($admin)
        ->test(PromoteStudents::class)
        ->set('selectedGrades', [$terminalGrade->id])
        ->call('promote');

    $student->refresh();
    expect($student->graduated_at)->not->toBeNull();
    expect($student->graduated_grade_id)->toBe($terminalGrade->id);

    // No new grade row is created -- there's nowhere to go -- but the old one is no longer current.
    expect(GradeStudent::where('student_id', $student->id)->count())->toBe(1);
    $old = GradeStudent::where('student_id', $student->id)->where('grade', $terminalGrade->id)->first();
    expect($old->is_current)->toBeFalsy();
});

test('graduating a student also drops their current school membership', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $terminalGrade = Grade::create(['grade' => 'GRADE 12']);
    $student = makeStudentInGrade($terminalGrade);
    $school = School::create(['name' => 'Test School']);
    SchoolStudent::create(['student_id' => $student->id, 'school_id' => $school->id, 'is_current' => true]);

    Livewire::actingAs($admin)
        ->test(PromoteStudents::class)
        ->set('selectedGrades', [$terminalGrade->id])
        ->call('promote');

    $current = SchoolStudent::where('student_id', $student->id)->where('school_id', $school->id)->first();
    expect($current->is_current)->toBeFalsy();
});

test('a single submit can promote some grades and graduate others at once', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $grade1 = Grade::create(['grade' => 'GRADE 1']);
    $grade2 = Grade::create(['grade' => 'GRADE 2']);
    $grade1->update(['next_grade_id' => $grade2->id]);
    $terminalGrade = Grade::create(['grade' => 'GRADE 12']);

    $promoted = makeStudentInGrade($grade1);
    $graduated = makeStudentInGrade($terminalGrade);

    Livewire::actingAs($admin)
        ->test(PromoteStudents::class)
        ->set('selectedGrades', [$grade1->id, $terminalGrade->id])
        ->call('promote');

    expect((int) GradeStudent::where('student_id', $promoted->id)->where('is_current', true)->first()->grade)->toBe($grade2->id);
    expect($graduated->fresh()->graduated_at)->not->toBeNull();
    expect((int) $graduated->fresh()->graduated_grade_id)->toBe($terminalGrade->id);
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

test('toggleSelectAll selects every grade with students, including terminal ones', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $grade1 = Grade::create(['grade' => 'GRADE 1']);
    $grade2 = Grade::create(['grade' => 'GRADE 2']);
    $grade1->update(['next_grade_id' => $grade2->id]);
    $terminal = Grade::create(['grade' => 'GRADE 12']);
    makeStudentInGrade($grade1);
    makeStudentInGrade($terminal);

    $component = Livewire::actingAs($admin)
        ->test(PromoteStudents::class)
        ->call('toggleSelectAll', true);

    expect($component->get('selectedGrades'))->toEqualCanonicalizing([$grade1->id, $terminal->id]);
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
