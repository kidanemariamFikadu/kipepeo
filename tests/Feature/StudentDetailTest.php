<?php

use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\School;
use App\Models\SchoolStudent;
use App\Models\Student;
use App\Models\StudentGuardian;
use App\Models\User;
use Illuminate\Support\Facades\DB;

function makeStudentWithRelations(string $name): Student
{
    $student = Student::create(['name' => $name, 'dob' => '2010-01-01', 'gender' => 'male']);

    StudentGuardian::create(['student_id' => $student->id, 'guardian_name' => 'G1', 'guardian_phone' => '111']);
    StudentGuardian::create(['student_id' => $student->id, 'guardian_name' => 'G2', 'guardian_phone' => '222']);

    foreach (['School A', 'School B'] as $schoolName) {
        $school = School::create(['name' => $schoolName]);
        SchoolStudent::create(['student_id' => $student->id, 'school_id' => $school->id, 'is_current' => false]);
    }

    foreach (['GRADE 1', 'GRADE 2'] as $gradeName) {
        $grade = Grade::create(['grade' => $gradeName]);
        GradeStudent::create(['student_id' => $student->id, 'grade' => $grade->id, 'is_current' => false]);
    }

    return $student;
}

test('student detail page shows the requested student, not an unrelated one', function () {
    $user = User::factory()->create(['role' => 'user']);
    $first = makeStudentWithRelations('Alpha Student');
    $second = makeStudentWithRelations('Beta Student');

    $response = $this->actingAs($user)->get("/student-detail/{$second->id}");

    $response->assertOk();
    $response->assertSee('Beta Student');
    $response->assertDontSee('Alpha Student');
});

test('student detail page does not N+1 query per related row', function () {
    $user = User::factory()->create(['role' => 'user']);
    $student = makeStudentWithRelations('Query Count Student');

    $queryCount = 0;
    DB::listen(function () use (&$queryCount) {
        $queryCount++;
    });

    $this->actingAs($user)->get("/student-detail/{$student->id}")->assertOk();

    // 2 guardians + 2 schools (with school) + 2 grades (with gradeTable) eager-loaded
    // should stay well under a per-row query count; N+1 would scale with row count instead.
    expect($queryCount)->toBeLessThan(15);
});

test('a graduated badge is shown for a graduated student', function () {
    $user = User::factory()->create(['role' => 'user']);
    $student = makeStudentWithRelations('Graduated Student');
    $student->update(['graduated_at' => '2026-06-01']);

    $response = $this->actingAs($user)->get("/student-detail/{$student->id}");

    $response->assertSee('Graduated 2026-06-01');
});

test('no graduated badge is shown for an active student', function () {
    $user = User::factory()->create(['role' => 'user']);
    $student = makeStudentWithRelations('Active Student');

    $response = $this->actingAs($user)->get("/student-detail/{$student->id}");

    $response->assertDontSee('Graduated ');
});
