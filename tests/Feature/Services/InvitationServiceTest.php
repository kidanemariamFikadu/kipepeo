<?php

use App\Mail\InviteMail;
use App\Models\Invite;
use App\Models\JobTitle;
use App\Models\User;
use App\Services\InvitationSerivce;
use Illuminate\Support\Facades\Mail;

test('create stores a pending invite and sends the invite email', function () {
    Mail::fake();
    $jobTitle = JobTitle::create(['name' => 'Teacher']);
    $admin = User::factory()->create(['role' => 'admin']);

    InvitationSerivce::create('invitee@example.com', 'user', $jobTitle->id, $admin->id);

    $invite = Invite::where('email', 'invitee@example.com')->first();
    expect($invite)->not->toBeNull();
    expect($invite->status)->toBe('pending');
    expect($invite->invited_by)->toBe($admin->id);

    Mail::assertSent(InviteMail::class);
});

test('accept converts a pending invite into a user account', function () {
    $jobTitle = JobTitle::create(['name' => 'Teacher']);
    $admin = User::factory()->create(['role' => 'admin']);
    $invite = Invite::create([
        'email' => 'invitee@example.com',
        'token' => 'tok123',
        'role' => 'user',
        'job_title_id' => $jobTitle->id,
        'invited_by' => $admin->id,
        'status' => 'pending',
        'expires_at' => now()->addDays(7),
    ]);

    InvitationSerivce::accept('tok123', 'New User', 'password123');

    expect(User::where('email', 'invitee@example.com')->exists())->toBeTrue();
    expect($invite->fresh()->status)->toBe('accepted');
});

test('resend re-sends the invite email', function () {
    Mail::fake();
    $jobTitle = JobTitle::create(['name' => 'Teacher']);
    $admin = User::factory()->create(['role' => 'admin']);
    $invite = Invite::create([
        'email' => 'invitee@example.com',
        'token' => 'tok123',
        'role' => 'user',
        'job_title_id' => $jobTitle->id,
        'invited_by' => $admin->id,
        'status' => 'pending',
        'expires_at' => now()->addDays(7),
    ]);

    InvitationSerivce::resend($invite->id);

    Mail::assertSent(InviteMail::class);
});

test('delete removes the invite by token', function () {
    $jobTitle = JobTitle::create(['name' => 'Teacher']);
    $admin = User::factory()->create(['role' => 'admin']);
    $invite = Invite::create([
        'email' => 'invitee@example.com',
        'token' => 'tok123',
        'role' => 'user',
        'job_title_id' => $jobTitle->id,
        'invited_by' => $admin->id,
        'status' => 'pending',
        'expires_at' => now()->addDays(7),
    ]);

    InvitationSerivce::delete('tok123');

    expect(Invite::find($invite->id))->toBeNull();
});
