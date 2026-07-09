<?php

use App\Livewire\User\CreateUser;
use App\Models\JobTitle;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('creating a user does not crash and gives them a usable password via a reset link', function () {
    Notification::fake();

    $admin = User::factory()->create(['role' => 'admin']);
    $jobTitle = JobTitle::create(['name' => 'Teacher']);

    Livewire::actingAs($admin)
        ->test(CreateUser::class)
        ->set('form.name', 'New Person')
        ->set('form.email', 'newperson@example.com')
        ->set('form.job_title_id', $jobTitle->id)
        ->set('form.role', 'user')
        ->call('create')
        ->assertHasNoErrors()
        ->assertDispatched('user-updated');

    $user = User::where('email', 'newperson@example.com')->first();
    expect($user)->not->toBeNull();
    expect($user->password)->not->toBeNull();

    Notification::assertSentTo($user, ResetPassword::class);
});
