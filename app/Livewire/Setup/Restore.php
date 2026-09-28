<?php

namespace App\Livewire\Setup;

use App\Models\User;
use App\Support\DatabaseBackup;
use Dotenv\Dotenv;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
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

            // Only touch .env once the import has actually succeeded, so a
            // failed restore doesn't leave the app pointed at a database
            // that was never populated. Merge an allow-list rather than
            // copying the uploaded file over: it is attacker-supplied, and
            // wholesale replacement would let it set APP_DEBUG, repoint
            // MAIL_*, or swap out any other application setting.
            $this->mergeRestorableEnv($envValues);

            Artisan::call('config:clear');
            Artisan::call('cache:clear');

            $this->done = true;
        } catch (Throwable $e) {
            $this->addError('backupFile', 'Restore failed: '.$e->getMessage());
        } finally {
            File::deleteDirectory($extractDir);
        }
    }

    /**
     * The only keys a restore archive is allowed to change.
     *
     * APP_KEY is included deliberately: Laravel seals two-factor secrets and
     * recovery codes with it, so importing a database without the key that
     * encrypted it leaves those columns permanently unreadable. Everything
     * else -- APP_DEBUG, MAIL_*, queue/cache/filesystem drivers -- stays
     * whatever this server already had.
     */
    private const RESTORABLE_ENV_KEYS = [
        'APP_KEY',
        'DB_CONNECTION',
        'DB_HOST',
        'DB_PORT',
        'DB_DATABASE',
        'DB_USERNAME',
        'DB_PASSWORD',
    ];

    private function mergeRestorableEnv(array $uploaded): void
    {
        File::put(base_path('.env'), self::mergeEnvString(File::get(base_path('.env')), $uploaded));
    }

    /**
     * Pure string merge, separated from the file I/O so the allow-list
     * behaviour can be tested without writing to the real .env.
     */
    public static function mergeEnvString(string $env, array $uploaded): string
    {
        foreach (self::RESTORABLE_ENV_KEYS as $key) {
            if (! array_key_exists($key, $uploaded)) {
                continue;
            }

            // Strip newlines before anything else -- one embedded in a value
            // would otherwise let a single key write additional settings.
            $value = '"'.addcslashes(str_replace(["\r", "\n"], '', (string) $uploaded[$key]), '\\"').'"';
            $pattern = '/^'.preg_quote($key, '/').'=.*$/m';

            $env = preg_match($pattern, $env) === 1
                ? preg_replace($pattern, $key.'='.$value, $env, 1)
                : rtrim($env, "\n")."\n".$key.'='.$value."\n";
        }

        return $env;
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
        // A missing users table means migrations haven't run -- a genuine
        // fresh install. Everything else must fail CLOSED: the previous
        // catch-all treated any Throwable as "empty", so a database outage
        // on a server that already had users silently reopened this
        // unauthenticated endpoint. Schema::hasTable() throws when the
        // connection is down, and that is deliberately left to propagate.
        if (! Schema::hasTable('users')) {
            return true;
        }

        return User::count() === 0;
    }

    public function render()
    {
        return view('livewire.setup.restore');
    }
}
