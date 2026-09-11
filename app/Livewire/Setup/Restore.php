<?php

namespace App\Livewire\Setup;

use App\Models\User;
use App\Support\DatabaseBackup;
use Dotenv\Dotenv;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Throwable;
use ZipArchive;

#[Layout('layouts.guest')]
#[Title('Restore from backup')]
class Restore extends Component
{
    use WithFileUploads;

    public $backupFile;

    public bool $done = false;

    /**
     * Reachable with no login only while the database is genuinely empty --
     * the moment a user exists, this 404s for everyone. Table-not-migrated
     * counts as empty too, so this also works on a server that's had
     * `migrate` run but nothing seeded yet.
     */
    public function mount()
    {
        abort_unless($this->databaseIsEmpty(), 404);
    }

    public function restore()
    {
        $this->resetErrorBag();

        // Re-check right before doing anything destructive: mount() only
        // runs on the initial page load, not on this submit.
        abort_unless($this->databaseIsEmpty(), 403);

        $this->validate([
            'backupFile' => ['required', 'file', 'extensions:zip', 'mimetypes:application/zip,application/x-zip-compressed,application/octet-stream'],
        ]);

        $extractDir = storage_path('app/tmp/restore-'.uniqid());
        mkdir($extractDir, 0755, true);

        try {
            $zip = new ZipArchive();
            if ($zip->open($this->backupFile->getRealPath()) !== true) {
                $this->addError('backupFile', 'Could not open the uploaded file as a zip archive.');

                return;
            }
            $zip->extractTo($extractDir);
            $zip->close();

            $sqlPath = $extractDir.'/database.sql';
            $envPath = $extractDir.'/.env';

            if (! is_file($sqlPath) || ! is_file($envPath)) {
                $this->addError('backupFile', "This doesn't look like a Kipepeo backup -- expected database.sql and .env inside the zip.");

                return;
            }

            $this->backupCurrentEnv();

            $envValues = Dotenv::parse(File::get($envPath));

            DatabaseBackup::restoreUsing(
                sql: File::get($sqlPath),
                host: $envValues['DB_HOST'] ?? '127.0.0.1',
                port: $envValues['DB_PORT'] ?? '3306',
                database: $envValues['DB_DATABASE'] ?? '',
                username: $envValues['DB_USERNAME'] ?? '',
                password: $envValues['DB_PASSWORD'] ?? '',
            );

            // Only swap .env once the import has actually succeeded, so a
            // failed restore doesn't leave the app pointed at a database
            // that was never populated.
            File::copy($envPath, base_path('.env'));

            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            $this->done = true;
        } catch (Throwable $e) {
            $this->addError('backupFile', 'Restore failed: '.$e->getMessage());
        } finally {
            File::deleteDirectory($extractDir);
        }
    }

    private function backupCurrentEnv(): void
    {
        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        File::copy(base_path('.env'), $backupDir.'/.env.pre-restore-'.now()->format('Ymd-His'));
    }

    private function databaseIsEmpty(): bool
    {
        try {
            return User::count() === 0;
        } catch (Throwable) {
            // Table doesn't exist yet (migrations not run) -- treat as empty.
            return true;
        }
    }

    public function render()
    {
        return view('livewire.setup.restore');
    }
}
