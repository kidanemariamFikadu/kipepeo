<?php

use App\Enums\UserRole;
use App\Livewire\User\EditUser;
use App\Models\JobTitle;
use App\Models\User;
use Livewire\Livewire;

test('role attribute is cast to the UserRole enum', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $user = User::factory()->create(['role' => 'user']);

    expect($admin->role)->toBe(UserRole::Admin);
    expect($admin->isAdmin())->toBeTrue();

    expect($user->role)->toBe(UserRole::User);
    expect($user->isAdmin())->toBeFalse();
});

test('edit user form receives a plain string role, not an enum instance', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $target = User::factory()->create(['role' => 'user']);
    $jobTitle = JobTitle::create(['name' => 'Teacher']);

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['user' => $target])
        ->assertSet('form.role', 'user')
        ->set('form.name', $target->name)
        ->set('form.job_title_id', $jobTitle->id)
        ->set('form.role', 'admin')
        ->call('update');

    expect($target->fresh()->role)->toBe(UserRole::Admin);
});
