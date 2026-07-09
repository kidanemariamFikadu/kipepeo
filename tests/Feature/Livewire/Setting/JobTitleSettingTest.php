<?php

use App\Livewire\Setting\Index as SettingIndex;
use App\Livewire\Setting\JobTitle as JobTitleComponent;
use App\Models\JobTitle;
use App\Models\User;
use Livewire\Livewire;

test('save creates a new job title', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(JobTitleComponent::class)
        ->set('jobTitle', 'Principal')
        ->call('save')
        ->assertDispatched('MessageChanged');

    expect(JobTitle::where('name', 'Principal')->exists())->toBeTrue();
});

test('save updates an existing job title', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $jobTitle = JobTitle::create(['name' => 'Old Name']);

    Livewire::actingAs($admin)
        ->test(JobTitleComponent::class, ['jobTitleId' => $jobTitle->id])
        ->set('jobTitle', 'New Name')
        ->call('save');

    expect($jobTitle->fresh()->name)->toBe('New Name');
});

test('removeJobTitle refuses to delete a job title assigned to a user', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $jobTitle = JobTitle::create(['name' => 'Teacher']);
    User::factory()->create(['job_title_id' => $jobTitle->id]);

    Livewire::actingAs($admin)
        ->test(SettingIndex::class)
        ->call('removeJobTitle', $jobTitle->id);

    expect(JobTitle::find($jobTitle->id))->not->toBeNull();
});

test('removeJobTitle deletes a job title with no assigned users', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $jobTitle = JobTitle::create(['name' => 'Unused Title']);

    Livewire::actingAs($admin)
        ->test(SettingIndex::class)
        ->call('removeJobTitle', $jobTitle->id);

    expect(JobTitle::find($jobTitle->id))->toBeNull();
});
