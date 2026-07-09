<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Throwable;

class DeployProduction extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'app:deploy
        {--branch=main : The git branch to pull}
        {--force : Skip the "are you sure" prompt when APP_ENV is not production}';

    /**
     * The console command description.
     */
    protected $description = 'Pull, build, and migrate the app in place: git pull, composer/npm install, migrate, cache rebuild.';

    public function handle(): int
    {
        if (! $this->confirmEnvironment()) {
            return self::FAILURE;
        }

        if (! $this->checkRequiredBinaries()) {
            return self::FAILURE;
        }

        $this->checkEnvSanity();

        $this->line('');
        $this->info('==> Enabling maintenance mode');
        Artisan::call('down', ['--render' => 'errors::503', '--retry' => 60]);

        try {
            $this->backupDatabase();
            $this->pullLatestCode();
            $this->installDependencies();
            $this->checkRoleData();
            $this->runMigrations();
            $this->rebuildCaches();
        } catch (Throwable $e) {
            $this->line('');
            $this->error('DEPLOY FAILED: '.$e->getMessage());
            $this->error('The site is still in maintenance mode. Fix the problem, then either re-run this command or run "php artisan up" once it is safe to serve traffic again.');

            return self::FAILURE;
        }

        $this->line('');
        $this->info('==> Disabling maintenance mode');
        Artisan::call('up');

        $this->line('');
        $this->info('Deploy complete.');
        $this->warn('Reminder: grades need a "next grade" configured under Settings > Grades before the "Promote Students" feature can move anyone up.');

        return self::SUCCESS;
    }

    private function confirmEnvironment(): bool
    {
        if (app()->environment('production') || $this->option('force')) {
            return true;
        }

        return $this->confirm(
            'APP_ENV is "'.app()->environment().'", not "production". Run the deploy anyway?',
            false
        );
    }

    private function checkRequiredBinaries(): bool
    {
        $this->info('==> Checking required tools are on PATH');

        $missing = [];
        foreach (['git', 'composer', 'npm', 'mysqldump'] as $binary) {
            if (! $this->binaryExists($binary)) {
                $missing[] = $binary;
            }
        }

        if ($missing !== []) {
            $this->error('Missing required tool(s) on PATH: '.implode(', ', $missing));

            return false;
        }

        return true;
    }

    private function binaryExists(string $binary): bool
    {
        $command = windows_os() ? "where {$binary}" : "command -v {$binary}";

        return Process::fromShellCommandline($command)->run() === 0;
    }

    private function checkEnvSanity(): void
    {
        if (blank(config('app.key'))) {
            throw new RuntimeException('APP_KEY is empty -- refusing to deploy against what looks like an unconfigured environment.');
        }

        if (config('app.debug')) {
            $this->warn('WARNING: APP_DEBUG is true -- stack traces will be shown to visitors.');
        }
    }

    private function backupDatabase(): void
    {
        $this->info('==> Backing up the database');

        $connection = config('database.connections.'.config('database.default'));

        $backupDir = storage_path('app/backups');
        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $backupFile = $backupDir.DIRECTORY_SEPARATOR.$connection['database'].'-'.now()->format('Ymd-His').'.sql';

        $process = new Process([
            'mysqldump',
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--user='.$connection['username'],
            '--routines',
            '--single-transaction',
            $connection['database'],
        ]);
        $process->setTimeout(null);
        $process->run(null, ['MYSQL_PWD' => $connection['password']]);

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        file_put_contents($backupFile, $process->getOutput());

        if (filesize($backupFile) === 0) {
            throw new \RuntimeException('Database backup file is empty -- aborting before touching the database.');
        }

        $this->line('Backup saved to '.$backupFile);
    }

    private function pullLatestCode(): void
    {
        $this->info('==> Pulling latest code from git');

        $status = $this->runProcess(['git', 'status', '--porcelain']);
        if (trim($status) !== '') {
            throw new \RuntimeException('Working tree has local changes on the server (git status --porcelain is non-empty). Resolve those before deploying so the pull is a clean fast-forward.');
        }

        $this->runProcess(['git', 'fetch', 'origin']);
        $this->runProcess(['git', 'pull', '--ff-only', 'origin', $this->option('branch')]);
    }

    private function installDependencies(): void
    {
        $this->info('==> Installing PHP dependencies');
        $this->runProcess(['composer', 'install', '--no-dev', '--optimize-autoloader', '--no-interaction'], null);

        $this->info('==> Installing JS dependencies and building frontend assets');
        $this->runProcess(['npm', 'ci'], null);
        $this->runProcess(['npm', 'run', 'build'], null);
    }

    private function checkRoleData(): void
    {
        $this->info("==> Checking existing 'role' values are compatible with the role enum");

        $badRoles = DB::table('users')->whereNotIn('role', ['admin', 'user'])->count();

        if ($badRoles > 0) {
            throw new \RuntimeException("Found {$badRoles} user(s) with a role value other than 'admin'/'user'. Fix that data before deploying -- the role enum cast will crash on any unexpected value.");
        }
    }

    private function runMigrations(): void
    {
        $this->info('==> Running database migrations');
        // Adds: deleted_at (soft deletes) to students/books/users, and
        // grades.next_grade_id (student promotion feature). Both are
        // additive, nullable columns -- safe against existing rows.
        Artisan::call('migrate', ['--force' => true], $this->output);
    }

    private function rebuildCaches(): void
    {
        $this->info('==> Rebuilding caches');
        Artisan::call('optimize:clear', [], $this->output);
        Artisan::call('config:cache', [], $this->output);
        Artisan::call('route:cache', [], $this->output);
        Artisan::call('view:cache', [], $this->output);

        if (! is_dir(public_path('storage'))) {
            Artisan::call('storage:link', [], $this->output);
        }
    }

    private function runProcess(array $command, ?int $timeout = 60): string
    {
        $process = new Process($command, base_path());
        $process->setTimeout($timeout);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }
}
