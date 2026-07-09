<?php

use App\Livewire\User\AcceptInvite;
use App\Models\Invite;
use App\Models\JobTitle;
use App\Models\User;
use Livewire\Livewire;

function makePendingInvite(array $overrides = []): Invite
{
    $jobTitle = JobTitle::create(['name' => 'Teacher']);
    $admin = User::factory()->create(['role' => 'admin']);

    return Invite::create(array_merge([
        'email' => 'invitee@example.com',
        'token' => 'validtoken',
        'role' => 'user',
        'job_title_id' => $jobTitle->id,
        'invited_by' => $admin->id,
        'status' => 'pending',
        'expires_at' => now()->addDays(7),
    ], $overrides));
}

test('accepting a valid invite creates a user and redirects to login', function () {
    $invite = makePendingInvite();

    Livewire::test(AcceptInvite::class, ['token' => $invite->token])
        ->set('form.name', 'New Person')
        ->set('form.password', 'password123')
        ->set('form.password_confirmation', 'password123')
        ->call('accept')
        ->assertRedirect(route('login'));

    expect(User::where('email', $invite->email)->exists())->toBeTrue();
    expect($invite->fresh()->status)->toBe('accepted');
});

test('accepting an expired invite flashes an error and creates no user', function () {
    $invite = makePendingInvite(['expires_at' => now()->subDay()]);

    Livewire::test(AcceptInvite::class, ['token' => $invite->token])
        ->set('form.name', 'New Person')
        ->set('form.password', 'password123')
        ->set('form.password_confirmation', 'password123')
        ->call('accept');

    expect(User::where('email', $invite->email)->exists())->toBeFalse();
});

test('accepting with an invalid token flashes an error', function () {
    Livewire::test(AcceptInvite::class, ['token' => 'does-not-exist'])
        ->set('form.name', 'New Person')
        ->set('form.password', 'password123')
        ->set('form.password_confirmation', 'password123')
        ->call('accept');

    expect(User::count())->toBe(0);
});
