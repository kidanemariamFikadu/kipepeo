<?php

use App\Livewire\Dashboard\InSessionComponent;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

test('in-session list shows a currently checked in student', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Present Student', 'dob' => '2012-01-01', 'gender' => 'male']);
    Attendance::create(['student_id' => $student->id, 'date' => now(), 'current_in' => true]);

    $component = Livewire::actingAs($user)->test(InSessionComponent::class);

    expect($component->viewData('inSessionStudents')->pluck('student.id'))->toContain($student->id);
});

test('in-session list does not crash when the checked in student was soft-deleted', function () {
    $user = User::factory()->create();
    $student = Student::create(['name' => 'Ghost Student', 'dob' => '2012-01-01', 'gender' => 'male']);
    $attendance = Attendance::create(['student_id' => $student->id, 'date' => now(), 'current_in' => true]);
    $student->delete();

    $component = Livewire::actingAs($user)->test(InSessionComponent::class);

    expect($component->viewData('inSessionStudents')->pluck('id'))->not->toContain($attendance->id);
});
