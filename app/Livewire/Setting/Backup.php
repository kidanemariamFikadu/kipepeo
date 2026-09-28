<?php

namespace App\Livewire\Setting;

use App\Support\DatabaseBackup;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Throwable;
use ZipArchive;

class Backup extends Component
{
    public ?string $error = null;

    /**
     * Bundles a fresh mysqldump with the current .env into one zip and
     * streams it back as a normal browser download -- nothing is written
     * anywhere the user didn't ask for.
     */
    public function downloadBackup()
    {
        $this->error = null;

        try {
            $sql = DatabaseBackup::dump();
        } catch (Throwable $e) {
            // The exception message embeds the full mysqldump command line
            // (host, port, user, database), so it is logged rather than
            // rendered back into the page.
            Log::error('Database backup failed', ['exception' => $e]);
            $this->error = 'Backup failed. Check the application log for details.';

            return;
        }

        $tmpDir = storage_path('app/tmp');
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $zipPath = $tmpDir.'/kipepeo-backup-'.now()->format('Ymd-His').'.zip';

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->error = 'Could not create the backup archive.';

            return;
        }

        $zip->addFromString('database.sql', $sql);
        $zip->addFromString('.env', $this->restorableEnv());
        $zip->close();

        return response()->download($zipPath, basename($zipPath))->deleteFileAfterSend(true);
    }

    /**
     * Only what a restore actually consumes. The archive used to carry the
     * whole production .env -- mail credentials and every other setting
     * included -- down to whoever clicked Download.
     */
    private function restorableEnv(): string
    {
        $connection = config('database.connections.'.config('database.default'));

        $values = [
            // Needed so two-factor secrets encrypted on this server stay
            // readable after the database is restored elsewhere.
            'APP_KEY' => config('app.key'),
            'DB_CONNECTION' => config('database.default'),
            'DB_HOST' => $connection['host'] ?? '',
            'DB_PORT' => (string) ($connection['port'] ?? ''),
            'DB_DATABASE' => $connection['database'] ?? '',
            'DB_USERNAME' => $connection['username'] ?? '',
            'DB_PASSWORD' => $connection['password'] ?? '',
        ];

        return collect($values)
            ->map(fn ($value, $key) => $key.'="'.addcslashes((string) $value, '\\"').'"')
            ->implode("\n")."\n";
    }

    public function render()
    {
        return view('livewire.setting.backup');
    }
}
