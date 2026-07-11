<?php

use App\Livewire\User\ForcePasswordReset;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('a user flagged to reset their password is redirected away from any page', function () {
    $user = User::factory()->create(['must_reset_password' => true]);

    $this->actingAs($user)->get('/dashboard')->assertRedirect(route('force-password-reset'));
    $this->actingAs($user)->get('/students')->assertRedirect(route('force-password-reset'));
});

test('a user not flagged to reset their password is not redirected', function () {
    $user = User::factory()->create(['must_reset_password' => false]);

    $this->actingAs($user)->get('/dashboard')->assertOk();
});

test('the force-password-reset page itself does not redirect', function () {
    $user = User::factory()->create(['must_reset_password' => true]);

    $this->actingAs($user)->get('/force-password-reset')->assertOk();
});

test('submitting the correct current password and a valid new password clears the flag and redirects home', function () {
    $user = User::factory()->create(['must_reset_password' => true]);

    Livewire::actingAs($user)
        ->test(ForcePasswordReset::class)
        ->set('state', [
            'current_password' => 'password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->call('updatePassword')
        ->assertRedirect(route('dashboard'));

    $user->refresh();
    expect($user->must_reset_password)->toBeFalse();
    expect(Hash::check('new-password', $user->password))->toBeTrue();
});

test('a wrong current password shows an error and does not clear the flag', function () {
    $user = User::factory()->create(['must_reset_password' => true]);

    Livewire::actingAs($user)
        ->test(ForcePasswordReset::class)
        ->set('state', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->call('updatePassword')
        ->assertHasErrors(['current_password']);

    expect($user->fresh()->must_reset_password)->toBeTrue();
});
