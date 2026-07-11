<?php

use App\Livewire\User\CreateUser;
use App\Models\JobTitle;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('admin creating a user sets the typed password directly and flags it for reset', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $jobTitle = JobTitle::create(['name' => 'Teacher']);

    Livewire::actingAs($admin)
        ->test(CreateUser::class)
        ->set('form.name', 'New Person')
        ->set('form.email', 'newperson@example.com')
        ->set('form.job_title_id', $jobTitle->id)
        ->set('form.role', 'user')
        ->set('form.password', 'a-known-password')
        ->set('form.password_confirmation', 'a-known-password')
        ->call('create')
        ->assertHasNoErrors()
        ->assertDispatched('user-updated');

    $user = User::where('email', 'newperson@example.com')->firstOrFail();
    expect(Hash::check('a-known-password', $user->password))->toBeTrue();
    expect($user->must_reset_password)->toBeTrue();
});

test('create requires a unique email', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $jobTitle = JobTitle::create(['name' => 'Teacher']);
    User::factory()->create(['email' => 'taken@example.com']);

    Livewire::actingAs($admin)
        ->test(CreateUser::class)
        ->set('form.name', 'New User')
        ->set('form.email', 'taken@example.com')
        ->set('form.job_title_id', $jobTitle->id)
        ->set('form.role', 'user')
        ->set('form.password', 'a-known-password')
        ->set('form.password_confirmation', 'a-known-password')
        ->call('create')
        ->assertHasErrors(['form.email']);
});

test('create user form requires a password', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $jobTitle = JobTitle::create(['name' => 'Teacher']);

    Livewire::actingAs($admin)
        ->test(CreateUser::class)
        ->set('form.name', 'New Person')
        ->set('form.email', 'newperson@example.com')
        ->set('form.job_title_id', $jobTitle->id)
        ->set('form.role', 'user')
        ->set('form.password', '')
        ->call('create')
        ->assertHasErrors(['form.password']);
});

test('create user form rejects a short password', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $jobTitle = JobTitle::create(['name' => 'Teacher']);

    Livewire::actingAs($admin)
        ->test(CreateUser::class)
        ->set('form.name', 'New Person')
        ->set('form.email', 'newperson@example.com')
        ->set('form.job_title_id', $jobTitle->id)
        ->set('form.role', 'user')
        ->set('form.password', 'short')
        ->set('form.password_confirmation', 'short')
        ->call('create')
        ->assertHasErrors(['form.password']);
});

test('create user form rejects a mismatched password confirmation', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $jobTitle = JobTitle::create(['name' => 'Teacher']);

    Livewire::actingAs($admin)
        ->test(CreateUser::class)
        ->set('form.name', 'New Person')
        ->set('form.email', 'newperson@example.com')
        ->set('form.job_title_id', $jobTitle->id)
        ->set('form.role', 'user')
        ->set('form.password', 'a-known-password')
        ->set('form.password_confirmation', 'different-password')
        ->call('create')
        ->assertHasErrors(['form.password']);
});
