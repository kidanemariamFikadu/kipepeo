<?php

use App\Livewire\Setup\Restore;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

test('the restore page is reachable with no login when there are no users yet', function () {
    $this->get('/restore')->assertOk()->assertSee('Restore from backup');
});

test('the restore page 404s once a user exists', function () {
    User::factory()->create();

    $this->get('/restore')->assertNotFound();
});

test('restore rejects a non-zip file before touching anything', function () {
    $envBefore = file_get_contents(base_path('.env'));

    Livewire::test(Restore::class)
        ->set('backupFile', UploadedFile::fake()->create('backup.pdf', 10))
        ->call('restore')
        ->assertHasErrors(['backupFile']);

    expect(file_get_contents(base_path('.env')))->toBe($envBefore);
});

test('restore rejects a zip that is missing database.sql and .env', function () {
    $zipPath = storage_path('app/testing-incomplete-backup.zip');

    $zip = new ZipArchive();
    $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString('irrelevant.txt', 'not a backup');
    $zip->close();

    $envBefore = file_get_contents(base_path('.env'));

    Livewire::test(Restore::class)
        ->set('backupFile', UploadedFile::fake()->createWithContent('backup.zip', file_get_contents($zipPath)))
        ->call('restore')
        ->assertHasErrors(['backupFile']);

    expect(file_get_contents(base_path('.env')))->toBe($envBefore);

    unlink($zipPath);
});

test('restore aborts if a user was created after the page loaded', function () {
    $component = Livewire::test(Restore::class);

    User::factory()->create();

    $zipPath = storage_path('app/testing-race-backup.zip');
    $zip = new ZipArchive();
    $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $zip->addFromString('database.sql', 'SELECT 1;');
    $zip->addFromString('.env', 'APP_NAME=Test');
    $zip->close();

    $envBefore = file_get_contents(base_path('.env'));

    $component->set('backupFile', UploadedFile::fake()->createWithContent('backup.zip', file_get_contents($zipPath)))
        ->call('restore')
        ->assertStatus(403);

    expect(file_get_contents(base_path('.env')))->toBe($envBefore);

    unlink($zipPath);
});
