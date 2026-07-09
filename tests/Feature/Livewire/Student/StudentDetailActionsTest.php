<?php

use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\School;
use App\Models\SchoolStudent;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\User;
use Livewire\Drawer\Utils;

// StudentDetail::mount()/render() read the student id straight from request()->route('student_id')
// rather than a mount() parameter, and Livewire::test() dispatches through a synthetic
// "/livewire-unit-test-endpoint/..." route that has no such parameter - so the component can't be
// constructed through the normal Livewire testing helper. Instead we drive it the way a real
// browser would: load the real page, pull the wire:snapshot out of the HTML, and post that
// snapshot plus a method call to Livewire's actual update endpoint.
function callStudentDetailMethod(User $user, Student $student, string $method, array $params = []): void
{
    $html = test()->actingAs($user)->get("/student-detail/{$student->id}")->getContent();
    $snapshot = Utils::extractAttributeDataFromHtml($html, 'wire:snapshot');

    test()->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
        ->actingAs($user)
        ->postJson('/livewire/update', [
            'components' => [[
                'snapshot' => json_encode($snapshot),
                'updates' => [],
                'calls' => [[
                    'path' => '',
                    'method' => $method,
                    'params' => $params,
                ]],
            ]],
        ]);
}

test('makeGuardianPrimary promotes the chosen guardian and demotes the rest', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $student = Student::create(['name' => 'Detail Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    $primary = StudentGuardian::create(['student_id' => $student->id, 'guardian_name' => 'A', 'guardian_phone' => '1', 'is_primary' => true]);
    $other = StudentGuardian::create(['student_id' => $student->id, 'guardian_name' => 'B', 'guardian_phone' => '2', 'is_primary' => false]);

    callStudentDetailMethod($admin, $student, 'makeGuardianPrimary', [$other->id]);

    expect($primary->fresh()->is_primary)->toBeFalsy();
    expect($other->fresh()->is_primary)->toBeTruthy();
});

test('non-admin cannot delete a guardian', function () {
    $user = User::factory()->create(['role' => 'user']);
    $student = Student::create(['name' => 'Detail Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    $guardian = StudentGuardian::create(['student_id' => $student->id, 'guardian_name' => 'A', 'guardian_phone' => '1', 'is_primary' => true]);

    callStudentDetailMethod($user, $student, 'deleteGuardian', [$guardian->id]);

    expect(StudentGuardian::find($guardian->id))->not->toBeNull();
});

test('admin can delete a guardian', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $student = Student::create(['name' => 'Detail Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    $guardian = StudentGuardian::create(['student_id' => $student->id, 'guardian_name' => 'A', 'guardian_phone' => '1', 'is_primary' => true]);

    callStudentDetailMethod($admin, $student, 'deleteGuardian', [$guardian->id]);

    expect(StudentGuardian::find($guardian->id))->toBeNull();
});

test('update persists changes to the student record', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $student = Student::create(['name' => 'Detail Student', 'dob' => '2010-01-01', 'gender' => 'male']);

    $html = test()->actingAs($admin)->get("/student-detail/{$student->id}")->getContent();
    $snapshot = Utils::extractAttributeDataFromHtml($html, 'wire:snapshot');

    test()->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
        ->actingAs($admin)
        ->postJson('/livewire/update', [
            'components' => [[
                'snapshot' => json_encode($snapshot),
                'updates' => [
                    'updateStudentForm.name' => 'Renamed Student',
                    'updateStudentForm.gender' => 'male',
                    'updateStudentForm.dob' => '2011-05-05',
                ],
                'calls' => [[
                    'path' => '',
                    'method' => 'update',
                    'params' => [],
                ]],
            ]],
        ]);

    expect($student->fresh()->name)->toBe('Renamed Student');
});

test('non-admin cannot delete a school association', function () {
    $user = User::factory()->create(['role' => 'user']);
    $student = Student::create(['name' => 'Detail Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    $school = School::create(['name' => 'Test School']);
    SchoolStudent::create(['student_id' => $student->id, 'school_id' => $school->id, 'is_current' => true]);

    callStudentDetailMethod($user, $student, 'deleteSchool', [$student->id, $school->id]);

    expect(SchoolStudent::where(['student_id' => $student->id, 'school_id' => $school->id])->exists())->toBeTrue();
});

test('non-admin cannot delete a grade association', function () {
    $user = User::factory()->create(['role' => 'user']);
    $student = Student::create(['name' => 'Detail Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    $grade = Grade::create(['grade' => 'GRADE 1']);
    GradeStudent::create(['student_id' => $student->id, 'grade' => $grade->id, 'is_current' => true]);

    callStudentDetailMethod($user, $student, 'deleteGrade', [$student->id, $grade->id]);

    expect(GradeStudent::where(['student_id' => $student->id, 'grade' => $grade->id])->exists())->toBeTrue();
});

test('admin can graduate a student who is currently in a final grade', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $student = Student::create(['name' => 'Detail Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    $terminalGrade = Grade::create(['grade' => 'GRADE 12']);
    GradeStudent::create(['student_id' => $student->id, 'grade' => $terminalGrade->id, 'is_current' => true]);
    $school = School::create(['name' => 'Test School']);
    SchoolStudent::create(['student_id' => $student->id, 'school_id' => $school->id, 'is_current' => true]);

    callStudentDetailMethod($admin, $student, 'graduate');

    $student->refresh();
    expect($student->graduated_at)->not->toBeNull();
    expect($student->graduated_grade_id)->toBe($terminalGrade->id);
    expect(GradeStudent::where(['student_id' => $student->id, 'grade' => $terminalGrade->id])->first()->is_current)->toBeFalsy();
    expect(SchoolStudent::where(['student_id' => $student->id, 'school_id' => $school->id])->first()->is_current)->toBeFalsy();
});

test('non-admin cannot graduate a student', function () {
    $user = User::factory()->create(['role' => 'user']);
    $student = Student::create(['name' => 'Detail Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    $terminalGrade = Grade::create(['grade' => 'GRADE 12']);
    GradeStudent::create(['student_id' => $student->id, 'grade' => $terminalGrade->id, 'is_current' => true]);

    callStudentDetailMethod($user, $student, 'graduate');

    expect($student->fresh()->graduated_at)->toBeNull();
});

test('graduate is a no-op for a student whose current grade still promotes onward', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $student = Student::create(['name' => 'Detail Student', 'dob' => '2010-01-01', 'gender' => 'male']);
    $grade1 = Grade::create(['grade' => 'GRADE 1']);
    $grade2 = Grade::create(['grade' => 'GRADE 2']);
    $grade1->update(['next_grade_id' => $grade2->id]);
    GradeStudent::create(['student_id' => $student->id, 'grade' => $grade1->id, 'is_current' => true]);

    callStudentDetailMethod($admin, $student, 'graduate');

    expect($student->fresh()->graduated_at)->toBeNull();
});

test('graduate is a no-op for a student who has already graduated', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $terminalGrade = Grade::create(['grade' => 'GRADE 12']);
    $student = Student::create([
        'name' => 'Detail Student',
        'dob' => '2010-01-01',
        'gender' => 'male',
        'graduated_at' => now()->subDay(),
        'graduated_grade_id' => $terminalGrade->id,
    ]);
    $originalGraduatedAt = $student->graduated_at;

    callStudentDetailMethod($admin, $student, 'graduate');

    expect($student->fresh()->graduated_at->eq($originalGraduatedAt))->toBeTrue();
});
