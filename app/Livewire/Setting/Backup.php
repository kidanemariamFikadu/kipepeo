<?php

namespace App\Livewire\Setting;

use App\Support\DatabaseBackup;
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
            $this->error = 'Backup failed: '.$e->getMessage();

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
        $zip->addFile(base_path('.env'), '.env');
        $zip->close();

        return response()->download($zipPath, basename($zipPath))->deleteFileAfterSend(true);
    }

    public function render()
    {
        return view('livewire.setting.backup');
    }
}
