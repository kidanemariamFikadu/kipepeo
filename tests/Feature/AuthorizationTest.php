<?php

use App\Models\Student;
use App\Models\User;
use Livewire\Livewire;

function makeUser(string $role): User
{
    return User::factory()->create(['role' => $role]);
}

test('non-admin routes to admin-only pages are forbidden', function () {
    $this->withoutMiddleware(\Laravel\Jetstream\Http\Middleware\AuthenticateSession::class);

    $user = makeUser('user');
    $admin = makeUser('admin');

    foreach ([
        '/users',
        '/user-create',
        '/settings',
        '/settings/schools',
        '/settings/grades',
        '/settings/volunteers',
        '/settings/activity-types',
        '/settings/job-titles',
        '/settings/import-students',
        '/settings/import-books',
        '/invitation',
        '/promote-students',
    ] as $path) {
        $this->actingAs($user)->get($path)->assertForbidden();
        $this->actingAs($admin)->get($path)->assertOk();
    }
});

test('non-admin cannot edit another user', function () {
    $this->withoutMiddleware(\Laravel\Jetstream\Http\Middleware\AuthenticateSession::class);

    $user = makeUser('user');
    $target = makeUser('user');

    $this->actingAs($user)->get("/edit-user/{$target->id}")->assertForbidden();
});

test('non-admin cannot delete a single student', function () {
    $user = makeUser('user');
    $student = Student::create(['name' => 'Test Student', 'dob' => '2010-01-01', 'gender' => 'male']);

    Livewire::actingAs($user)
        ->test(\App\Livewire\StudentList::class)
        ->call('deleteRecord', $student->id)
        ->assertForbidden();

    expect(Student::find($student->id))->not->toBeNull();
});

test('admin can delete a single student', function () {
    $admin = makeUser('admin');
    $student = Student::create(['name' => 'Test Student', 'dob' => '2010-01-01', 'gender' => 'male']);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\StudentList::class)
        ->call('deleteRecord', $student->id);

    expect(Student::find($student->id))->toBeNull();
});

test('non-admin cannot mass-delete students', function () {
    $user = makeUser('user');
    $student = Student::create(['name' => 'Test Student', 'dob' => '2010-01-01', 'gender' => 'male']);

    Livewire::actingAs($user)
        ->test(\App\Livewire\StudentList::class)
        ->set('selectedStudents', [$student->id])
        ->call('deleteSelected')
        ->assertForbidden();

    expect(Student::find($student->id))->not->toBeNull();
});
