<?php

use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\School;
use App\Models\SchoolStudent;
use App\Models\Student;
use App\Models\User;

function makeSchoolStudent(School $school, string $name, bool $isCurrent): Student
{
    $student = Student::create(['name' => $name, 'dob' => '2010-01-01', 'gender' => 'male']);
    SchoolStudent::create(['school_id' => $school->id, 'student_id' => $student->id, 'is_current' => $isCurrent]);

    return $student;
}

test('school detail page shows the requested school and its students, not an unrelated school', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $first = School::create(['name' => 'Alpha School']);
    $second = School::create(['name' => 'Beta School']);
    makeSchoolStudent($first, 'Alpha Student', true);
    makeSchoolStudent($second, 'Beta Student', true);

    $response = $this->actingAs($admin)->get("/school-detail/{$second->id}");

    $response->assertOk();
    $response->assertSee('Beta School');
    $response->assertSee('Beta Student');
    $response->assertDontSee('Alpha Student');
});

test('a non-current school membership shows a Past badge, not Current', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $school = School::create(['name' => 'Mixed Roster School']);
    makeSchoolStudent($school, 'Currently Enrolled', true);
    makeSchoolStudent($school, 'Formerly Enrolled', false);

    $response = $this->actingAs($admin)->get("/school-detail/{$school->id}");

    $response->assertSeeInOrder(['Currently Enrolled', 'Current', 'Formerly Enrolled', 'Past']);
});

test('school detail page shows the current grade for a student', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $school = School::create(['name' => 'Graded School']);
    $student = makeSchoolStudent($school, 'Graded Student', true);
    $grade = Grade::create(['grade' => 'Grade 4']);
    GradeStudent::create(['student_id' => $student->id, 'grade' => $grade->id, 'is_current' => true]);

    $response = $this->actingAs($admin)->get("/school-detail/{$school->id}");

    $response->assertSee('Grade 4');
});

test('requesting a non-existent school 404s', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get('/school-detail/999999')->assertNotFound();
});

test('a non-admin is forbidden from the school detail page', function () {
    $user = User::factory()->create(['role' => 'user']);
    $school = School::create(['name' => 'Restricted School']);

    $this->actingAs($user)->get("/school-detail/{$school->id}")->assertForbidden();
});
