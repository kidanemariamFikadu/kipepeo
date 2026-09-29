<?php

use App\Livewire\Dashboard\AttendingStudentsBySchool;
use App\Livewire\Dashboard\BirthdayComponent;
use App\Livewire\Dashboard\InSessionComponent;
use App\Models\Attendance;
use App\Models\School;
use App\Models\SchoolStudent;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

test('a student in session links through to their detail page', function () {
    $user = User::factory()->create(['role' => 'user']);
    $student = Student::create(['name' => 'Present Student', 'dob' => '2012-01-01', 'gender' => 'male']);
    Attendance::create(['student_id' => $student->id, 'date' => now(), 'current_in' => true]);

    $html = Livewire::actingAs($user)->test(InSessionComponent::class)->html();

    expect($html)->toContain(route('student-detail', $student->id));
});

test('a student with a birthday this week links through to their detail page', function () {
    $user = User::factory()->create(['role' => 'user']);
    $student = Student::create([
        'name' => 'Birthday Student',
        'dob' => now()->addDays(2)->subYears(10)->format('Y-m-d'),
        'gender' => 'female',
    ]);

    $html = Livewire::actingAs($user)->test(BirthdayComponent::class)->html();

    expect($html)->toContain(route('student-detail', $student->id));
});

test('an admin can click a school through to its detail page', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $school = School::create(['name' => 'Linked School']);
    $student = Student::create(['name' => 'Attending Student', 'dob' => '2012-01-01', 'gender' => 'male']);
    SchoolStudent::create(['student_id' => $student->id, 'school_id' => $school->id, 'is_current' => true]);
    Attendance::create(['student_id' => $student->id, 'date' => now(), 'current_in' => true]);

    $html = Livewire::actingAs($admin)->test(AttendingStudentsBySchool::class)->html();

    expect($html)->toContain(route('school-detail', $school->id));
});

test('a non-admin sees the school name as plain text, not a link that would 403', function () {
    // school-detail sits behind the admin middleware, so linking it for
    // everyone would send staff to a forbidden page from their own dashboard.
    $staff = User::factory()->create(['role' => 'user']);
    $school = School::create(['name' => 'Linked School']);
    $student = Student::create(['name' => 'Attending Student', 'dob' => '2012-01-01', 'gender' => 'male']);
    SchoolStudent::create(['student_id' => $student->id, 'school_id' => $school->id, 'is_current' => true]);
    Attendance::create(['student_id' => $student->id, 'date' => now(), 'current_in' => true]);

    $html = Livewire::actingAs($staff)->test(AttendingStudentsBySchool::class)->html();

    expect($html)->not->toContain(route('school-detail', $school->id));
    // The row is still there and still readable.
    expect($html)->toContain('Linked School');

    // And the route really is admin-only, which is why the link is withheld.
    $this->actingAs($staff)->get(route('school-detail', $school->id))->assertForbidden();
});
