<?php

use App\Livewire\Dashboard\GreetingComponent;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

function greetingStudent(string $name): Student
{
    return Student::create(['name' => $name, 'dob' => '2014-05-05', 'gender' => 'female']);
}

test('the greeting counts today only, and averages the time present', function () {
    // 30 min, 60 min and 0 -- an average of 30 minutes exactly.
    foreach ([1800, 3600, 0] as $i => $seconds) {
        Attendance::create([
            'student_id' => greetingStudent("Today {$i}")->id,
            'date' => now()->toDateString(),
            'current_in' => $i < 2,
            'total_time' => $seconds,
        ]);
    }

    // Yesterday must not be counted into either number.
    Attendance::create([
        'student_id' => greetingStudent('Yesterday')->id,
        'date' => now()->subDay()->toDateString(),
        'current_in' => true,
        'total_time' => 99999,
    ]);

    Livewire::actingAs(User::factory()->create())
        ->test(GreetingComponent::class, ['name' => 'Kidane'])
        ->assertViewHas('totalStudents', 3)
        ->assertViewHas('studentsInAttendanceToday', 2)
        ->assertViewHas('averageAttendanceDuration', '00:30:00')
        ->assertViewHas('totalEnrolled', 4);
});

test('the greeting reports no average when nobody has attended today', function () {
    greetingStudent('Nobody Here');

    Livewire::actingAs(User::factory()->create())
        ->test(GreetingComponent::class, ['name' => 'Kidane'])
        ->assertViewHas('totalStudents', 0)
        ->assertViewHas('studentsInAttendanceToday', 0)
        ->assertViewHas('averageAttendanceDuration', '00:00:00');
});
