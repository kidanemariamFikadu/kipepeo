<?php

use App\Models\User;

test('any authenticated user can view the manual, without the admin-only sections', function () {
    $user = User::factory()->create(['role' => 'user']);

    $response = $this->actingAs($user)->get('/manual');

    $response->assertOk()
        ->assertSee('User Manual')
        ->assertSee('Getting started')
        // The roles table mentions "Backup & restore" for everyone (to show
        // what only admins can do) - what must NOT leak is the admin-only
        // section's own content.
        ->assertDontSee('Promote Students')
        ->assertDontSee('storage/app/backups/');
});

test('an admin also sees the admin-only sections of the manual', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get('/manual')
        ->assertOk()
        ->assertSee('Promote Students')
        ->assertSee('storage/app/backups/');
});
