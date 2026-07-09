<?php

use App\Livewire\User\Invitation;
use App\Mail\InviteMail;
use App\Models\Invite;
use App\Models\JobTitle;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

test('create sends an invitation and stores it as pending', function () {
    Mail::fake();
    $admin = User::factory()->create(['role' => 'admin']);
    $jobTitle = JobTitle::create(['name' => 'Teacher']);

    Livewire::actingAs($admin)
        ->test(Invitation::class)
        ->set('form.email', 'invitee@example.com')
        ->set('form.job_title_id', $jobTitle->id)
        ->set('form.role', 'user')
        ->call('create');

    expect(Invite::where('email', 'invitee@example.com')->where('status', 'pending')->exists())->toBeTrue();
    Mail::assertSent(InviteMail::class);
});

test('create rejects an email that already belongs to a user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $jobTitle = JobTitle::create(['name' => 'Teacher']);
    User::factory()->create(['email' => 'existing@example.com']);

    Livewire::actingAs($admin)
        ->test(Invitation::class)
        ->set('form.email', 'existing@example.com')
        ->set('form.job_title_id', $jobTitle->id)
        ->set('form.role', 'user')
        ->call('create')
        ->assertHasErrors(['form.email']);
});

test('deleteInvitation removes the invite', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $jobTitle = JobTitle::create(['name' => 'Teacher']);
    $invite = Invite::create([
        'email' => 'invitee@example.com',
        'token' => 'tok123',
        'role' => 'user',
        'job_title_id' => $jobTitle->id,
        'invited_by' => $admin->id,
        'status' => 'pending',
        'expires_at' => now()->addDays(7),
    ]);

    Livewire::actingAs($admin)
        ->test(Invitation::class)
        ->call('deleteInvitation', $invite->id);

    expect(Invite::find($invite->id))->toBeNull();
});

test('sendInvitation resends the invite email', function () {
    Mail::fake();
    $admin = User::factory()->create(['role' => 'admin']);
    $jobTitle = JobTitle::create(['name' => 'Teacher']);
    $invite = Invite::create([
        'email' => 'invitee@example.com',
        'token' => 'tok123',
        'role' => 'user',
        'job_title_id' => $jobTitle->id,
        'invited_by' => $admin->id,
        'status' => 'pending',
        'expires_at' => now()->addDays(7),
    ]);

    Livewire::actingAs($admin)
        ->test(Invitation::class)
        ->call('sendInvitation', $invite->id);

    Mail::assertSent(InviteMail::class);
});
