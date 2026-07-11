<?php

use App\Livewire\User\CreateUser;
use App\Livewire\User\EditUser;
use App\Models\JobTitle;
use App\Models\User;
use Livewire\Livewire;

test('create user form rejects a role outside the UserRole enum', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $jobTitle = JobTitle::create(['name' => 'Teacher']);

    Livewire::actingAs($admin)
        ->test(CreateUser::class)
        ->set('form.name', 'New Person')
        ->set('form.email', 'newperson@example.com')
        ->set('form.job_title_id', $jobTitle->id)
        ->set('form.role', 'superadmin')
        ->set('form.password', 'password123')
        ->set('form.password_confirmation', 'password123')
        ->call('create')
        ->assertHasErrors(['form.role']);
});

test('create user form accepts every real UserRole value', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $jobTitle = JobTitle::create(['name' => 'Teacher']);

    foreach (['admin', 'user'] as $i => $role) {
        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->set('form.name', 'New Person ' . $i)
            ->set('form.email', "newperson{$i}@example.com")
            ->set('form.job_title_id', $jobTitle->id)
            ->set('form.role', $role)
            ->set('form.password', 'password123')
            ->set('form.password_confirmation', 'password123')
            ->call('create')
            ->assertHasNoErrors();
    }
});

test('edit user form rejects a role outside the UserRole enum', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $target = User::factory()->create(['role' => 'user']);
    $jobTitle = JobTitle::create(['name' => 'Teacher']);

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['user' => $target])
        ->set('form.name', $target->name)
        ->set('form.job_title_id', $jobTitle->id)
        ->set('form.role', 'superadmin')
        ->call('update')
        ->assertHasErrors(['form.role']);
});
