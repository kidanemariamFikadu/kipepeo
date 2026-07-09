<?php

use App\Models\Invite;
use App\Models\JobTitle;
use App\Models\User;
use App\Mail\InviteMail;
use Illuminate\Support\Facades\Mail;

function makeInvite(array $overrides = []): Invite
{
    $jobTitle = JobTitle::create(['name' => 'Teacher']);
    $admin = User::factory()->create(['role' => 'admin']);

    return Invite::create(array_merge([
        'email' => 'invitee@example.com',
        'token' => 'abc123',
        'role' => 'user',
        'job_title_id' => $jobTitle->id,
        'invited_by' => $admin->id,
        'status' => 'pending',
        'expires_at' => now()->addDays(7),
    ], $overrides));
}

test('sendEmail dispatches an InviteMail to the invite email address', function () {
    Mail::fake();
    $invite = makeInvite();

    $invite->sendEmail();

    Mail::assertSent(InviteMail::class, function ($mail) use ($invite) {
        return $mail->hasTo($invite->email);
    });
});

test('accept creates a user with the invite role and job title, and marks the invite accepted', function () {
    $invite = makeInvite(['role' => 'admin']);

    $invite->accept('New Person', 'password123');

    $user = User::where('email', $invite->email)->first();
    expect($user)->not->toBeNull();
    expect($user->name)->toBe('New Person');
    expect($user->role->value)->toBe('admin');
    expect($user->job_title_id)->toBe($invite->job_title_id);

    expect($invite->fresh()->status)->toBe('accepted');
});

test('search scope matches by email', function () {
    makeInvite(['email' => 'findme@example.com', 'token' => 'tok1']);
    makeInvite(['email' => 'other@example.com', 'token' => 'tok2']);

    $results = Invite::search('findme')->get();

    expect($results)->toHaveCount(1);
});
