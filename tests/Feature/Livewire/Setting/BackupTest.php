<?php

use App\Livewire\Setting\Backup;
use App\Models\User;
use Livewire\Livewire;

test('an admin can download a backup containing the database dump and .env', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    Livewire::actingAs($admin)
        ->test(Backup::class)
        ->call('downloadBackup')
        ->assertFileDownloaded();
});

test('a non-admin cannot reach the backup page', function () {
    $user = User::factory()->create(['role' => 'user']);

    $this->actingAs($user)->get('/settings/backup')->assertForbidden();
});
