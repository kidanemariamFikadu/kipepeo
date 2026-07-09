<?php

use App\Models\JobTitle;
use App\Models\User;

test('search scope matches by name or email', function () {
    User::factory()->create(['name' => 'Zebra Person', 'email' => 'unrelated@example.com']);
    User::factory()->create(['name' => 'Someone Else', 'email' => 'zebra@example.com']);
    User::factory()->create(['name' => 'No Match', 'email' => 'no-match@example.com']);

    $results = User::search('Zebra')->get();

    expect($results)->toHaveCount(2);
});

test('jobTitle relationship resolves the assigned job title', function () {
    $jobTitle = JobTitle::create(['name' => 'Teacher']);
    $user = User::factory()->create(['job_title_id' => $jobTitle->id]);

    expect($user->jobTitle->id)->toBe($jobTitle->id);
});
