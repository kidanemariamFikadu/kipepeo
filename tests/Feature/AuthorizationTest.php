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

test('non-admin cannot mount or drive the admin-only modal components', function () {
    // Regression test for a privilege-escalation chain: the modal host is
    // rendered on every page, and Livewire does not re-apply the `admin`
    // middleware to /livewire/update -- so any logged-in user could mount
    // these components directly and act as an admin. Each now carries its
    // own check; route middleware alone is not enough.
    $user = makeUser('user');
    $target = makeUser('user');

    Livewire::actingAs($user)->test(\App\Livewire\User\EditUser::class, ['user' => $target])->assertForbidden();
    Livewire::actingAs($user)->test(\App\Livewire\User\CreateUser::class)->assertForbidden();
    Livewire::actingAs($user)->test(\App\Livewire\User\UserHistory::class, ['user' => $target])->assertForbidden();

    foreach ([
        \App\Livewire\Setting\School::class,
        \App\Livewire\Setting\Grade::class,
        \App\Livewire\Setting\JobTitle::class,
        \App\Livewire\Setting\ActivityType::class,
        \App\Livewire\Setting\BookCategory::class,
        \App\Livewire\Setting\Volunteer::class,
    ] as $component) {
        Livewire::actingAs($user)->test($component)->assertForbidden();
    }
});

test('a non-admin cannot promote themselves through EditUser', function () {
    $user = makeUser('user');

    Livewire::actingAs($user)
        ->test(\App\Livewire\User\EditUser::class, ['user' => $user])
        ->assertForbidden();

    expect($user->fresh()->role->value)->toBe('user');
});

test('an admin can still edit a user and change their role', function () {
    $admin = makeUser('admin');
    $target = makeUser('user');
    $jobTitle = \App\Models\JobTitle::create(['name' => 'Facilitator']);

    Livewire::actingAs($admin)
        ->test(\App\Livewire\User\EditUser::class, ['user' => $target])
        ->set('form.name', $target->name)
        ->set('form.job_title_id', $jobTitle->id)
        ->set('form.role', 'admin')
        ->call('update')
        ->assertHasNoErrors();

    expect($target->fresh()->role->value)->toBe('admin');
});

test('the audit trail never records credential columns', function () {
    // These rows are rendered in the user history screen, so anything
    // captured here is readable by anyone who can open it.
    //
    // Auditing is off for console events, and the test suite runs in the
    // console -- without this the audit set comes back empty and every
    // assertion below passes vacuously.
    config(['audit.console' => true]);

    $admin = makeUser('admin');
    $admin->update(['name' => 'Renamed', 'password' => bcrypt('a-new-password')]);
    $admin->update(['remember_token' => 'a-fresh-token']);

    $recorded = $admin->audits()
        ->get()
        ->flatMap(fn ($audit) => array_merge(
            array_keys($audit->old_values ?? []),
            array_keys($audit->new_values ?? []),
        ))
        ->unique();

    expect($recorded)->not->toContain('password');
    expect($recorded)->not->toContain('remember_token');
    expect($recorded)->not->toContain('two_factor_secret');
    expect($recorded)->not->toContain('two_factor_recovery_codes');
    // The non-sensitive change is still audited, so history stays useful.
    expect($recorded)->toContain('name');
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
