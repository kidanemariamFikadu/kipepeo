<?php

use App\Livewire\User\CreateUser;
use App\Models\JobTitle;
use App\Models\User;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;

test('create persists a new user with a random password and emails them a reset link', function () {
    Password::shouldReceive('sendResetLink')->once()->with(['email' => 'new.user@example.com']);
    $admin = User::factory()->create(['role' => 'admin']);
    $jobTitle = JobTitle::create(['name' => 'Teacher']);

    Livewire::actingAs($admin)
        ->test(CreateUser::class)
        ->set('form.name', 'New User')
        ->set('form.email', 'new.user@example.com')
        ->set('form.job_title_id', $jobTitle->id)
        ->set('form.role', 'user')
        ->call('create')
        ->assertDispatched('user-updated');

    $user = User::where('email', 'new.user@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('New User');
    expect($user->role->value)->toBe('user');
    expect($user->password)->not->toBeNull();
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
        ->call('create')
        ->assertHasErrors(['form.email']);
});
