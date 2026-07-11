<?php

use App\Livewire\User\ResetUserPassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('admin resetting a user\'s password updates the hash and flags it for reset', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $target = User::factory()->create(['must_reset_password' => false]);

    Livewire::actingAs($admin)
        ->test(ResetUserPassword::class, ['user' => $target])
        ->set('form.password', 'a-new-password')
        ->set('form.password_confirmation', 'a-new-password')
        ->call('resetPassword')
        ->assertHasNoErrors()
        ->assertDispatched('user-updated');

    $target->refresh();
    expect(Hash::check('a-new-password', $target->password))->toBeTrue();
    expect($target->must_reset_password)->toBeTrue();
});

test('a non-admin is forbidden from resetting a user\'s password', function () {
    $user = User::factory()->create(['role' => 'user']);
    $target = User::factory()->create();

    Livewire::actingAs($user)
        ->test(ResetUserPassword::class, ['user' => $target])
        ->assertForbidden();
});

test('reset password form rejects a short password', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $target = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(ResetUserPassword::class, ['user' => $target])
        ->set('form.password', 'short')
        ->set('form.password_confirmation', 'short')
        ->call('resetPassword')
        ->assertHasErrors(['form.password']);
});

test('reset password form rejects a mismatched confirmation', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $target = User::factory()->create();

    Livewire::actingAs($admin)
        ->test(ResetUserPassword::class, ['user' => $target])
        ->set('form.password', 'a-new-password')
        ->set('form.password_confirmation', 'different-password')
        ->call('resetPassword')
        ->assertHasErrors(['form.password']);
});
