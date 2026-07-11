<?php

use App\Livewire\Setting\Index as SettingIndex;
use App\Models\ActivityType;
use App\Models\Grade;
use App\Models\JobTitle;
use App\Models\School;
use App\Models\User;
use App\Models\Volunteer;
use Livewire\Livewire;

test('the settings hub counts each section correctly', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    School::create(['name' => 'Kipepeo Primary']);
    School::create(['name' => 'Riverside Secondary']);
    Grade::create(['grade' => 'Grade 1']);
    Volunteer::create(['name' => 'Test Volunteer', 'status' => 'active']);
    ActivityType::create(['name' => 'Cleaning']);
    JobTitle::create(['name' => 'Teacher']);

    $component = Livewire::actingAs($admin)->test(SettingIndex::class);

    expect($component->get('schoolCount'))->toBe(2);
    expect($component->get('gradeCount'))->toBe(1);
    expect($component->get('volunteerCount'))->toBe(1);
    expect($component->get('activityTypeCount'))->toBe(1);
    expect($component->get('jobTitleCount'))->toBe(1);
});
