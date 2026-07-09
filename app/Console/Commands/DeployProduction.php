<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

    /**
     * Tables checked for unexpected data loss around the migrate step.
     */
    private const CORE_TABLES = ['students', 'books', 'users', 'grades', 'schools', 'attendances', 'rentals'];

    private string $mysqldumpBinary = 'mysqldump';

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
        foreach (['git', 'composer', 'npm'] as $binary) {
            if (! $this->binaryExists($binary)) {
                $missing[] = $binary;
            }
        }

        $mysqldump = $this->locateMysqldump();
        if ($mysqldump === null) {
            $missing[] = 'mysqldump (checked PATH and common MySQL/MariaDB/XAMPP/Laragon/Herd install locations)';
        } else {
            $this->mysqldumpBinary = $mysqldump;
            if ($mysqldump !== 'mysqldump') {
                $this->line("Found mysqldump at {$mysqldump} (not on PATH, using this directly)");
            }
        }

        if ($missing !== []) {
            $this->error('Missing required tool(s): '.implode(', ', $missing));

            return false;
        }

        return true;
    }

    private function binaryExists(string $binary): bool
    {
        $command = windows_os() ? "where {$binary}" : "command -v {$binary}";

        return Process::fromShellCommandline($command)->run() === 0;
    }

    /**
     * mysqldump is frequently missing from PATH on Windows even when MySQL
     * is installed, since the installer doesn't always add it. Fall back to
     * searching the common install locations before giving up.
     */
    private function locateMysqldump(): ?string
    {
        if ($this->binaryExists('mysqldump')) {
            return 'mysqldump';
        }

        if (! windows_os()) {
            return null;
        }

        $home = getenv('USERPROFILE') ?: '';

        $patterns = [
            'C:\\Program Files\\MySQL\\MySQL Server *\\bin\\mysqldump.exe',
            'C:\\Program Files\\MariaDB *\\bin\\mysqldump.exe',
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\laragon\\bin\\mysql\\*\\bin\\mysqldump.exe',
            $home.'\\.config\\herd\\bin\\mysqldump.exe',
            $home.'\\.config\\herd\\services\\mysql\\*\\bin\\mysqldump.exe',
        ];

        foreach ($patterns as $pattern) {
            $matches = glob($pattern);
            if ($matches !== false && $matches !== []) {
                return $matches[0];
            }
        }

        return null;
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
            $this->mysqldumpBinary,
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
            throw new RuntimeException('Database backup file is empty -- aborting before touching the database.');
        }

        $this->line('Backup saved to '.$backupFile);
    }

    private function pullLatestCode(): void
    {
        $this->info('==> Pulling latest code from git');

        $status = $this->runProcess(['git', 'status', '--porcelain']);
        if (trim($status) !== '') {
            throw new RuntimeException(
                "Working tree has local changes on the server, refusing to pull:\n".$status.
                "\nResolve those (commit, stash, or discard them) before deploying so the pull is a clean fast-forward."
            );
        }

        $this->runProcess(['git', 'fetch', 'origin']);
        $this->runProcess(['git', 'pull', '--ff-only', 'origin', $this->option('branch')]);
    }

    private function installDependencies(): void
    {
        $this->info('==> Checking PHP version against locked dependencies');
        $this->checkPlatformRequirements();

        $this->info('==> Installing PHP dependencies');
        $this->runProcess(['composer', 'install', '--no-dev', '--optimize-autoloader', '--no-interaction'], null);

        $this->info('==> Installing JS dependencies and building frontend assets');
        $this->runProcess(['npm', 'ci'], null);
        $this->runProcess(['npm', 'run', 'build'], null);
    }

    /**
     * composer install's own error output when composer.lock doesn't
     * support the running PHP version is a wall of "Problem 1/2/3..." text
     * that doesn't say what to actually do about it. Check this up front
     * and fail with an actionable message instead.
     */
    private function checkPlatformRequirements(): void
    {
        $process = new Process(['composer', 'check-platform-reqs', '--lock', '--no-dev'], base_path());
        $process->setTimeout(60);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException(
                'PHP '.PHP_VERSION." doesn't satisfy the versions locked in composer.lock:\n"
                .trim($process->getOutput().$process->getErrorOutput())
                ."\n\nEither pin this site back to the PHP version composer.lock was built against "
                .'(e.g. `herd isolate 8.3`), or update composer.lock for the new PHP version from a dev '
                .'machine, commit it, and redeploy.'
            );
        }
    }

    private function checkRoleData(): void
    {
        $this->info("==> Checking existing 'role' values are compatible with the role enum");

        $badRoles = DB::table('users')->whereNotIn('role', ['admin', 'user'])->count();

        if ($badRoles > 0) {
            throw new RuntimeException("Found {$badRoles} user(s) with a role value other than 'admin'/'user'. Fix that data before deploying -- the role enum cast will crash on any unexpected value.");
        }
    }

    private function runMigrations(): void
    {
        $this->info('==> Running database migrations');

        $before = $this->snapshotRowCounts();

        // Adds: deleted_at (soft deletes) to students/books/users, and
        // grades.next_grade_id (student promotion feature). Both are
        // additive, nullable columns -- safe against existing rows.
        Artisan::call('migrate', ['--force' => true], $this->output);

        $this->verifyNoDataLoss($before, $this->snapshotRowCounts());
    }

    /**
     * @return array<string, int|null> table name => row count, or null if the table doesn't exist
     */
    private function snapshotRowCounts(): array
    {
        $counts = [];
        foreach (self::CORE_TABLES as $table) {
            $counts[$table] = Schema::hasTable($table) ? DB::table($table)->count() : null;
        }

        return $counts;
    }

    /**
     * @param  array<string, int|null>  $before
     * @param  array<string, int|null>  $after
     */
    private function verifyNoDataLoss(array $before, array $after): void
    {
        $this->info('==> Verifying no table lost data');

        $emptied = [];
        foreach ($before as $table => $countBefore) {
            $countAfter = $after[$table] ?? null;
            if ($countBefore === null || $countAfter === null) {
                continue;
            }

            $this->line(sprintf('  %-12s %6d -> %-6d %s', $table, $countBefore, $countAfter, $countAfter < $countBefore ? '!!' : 'OK'));

            if ($countBefore > 0 && $countAfter === 0) {
                $emptied[] = $table;
            }
        }

        if ($emptied === []) {
            return;
        }

        $this->warn('WARNING: the following table(s) had data before migrating and are now empty: '.implode(', ', $emptied));
        $this->warn('The pre-deploy backup taken at the start of this run is in storage/app/backups/ if you need to restore.');

        if (! $this->confirm('Continue the deploy anyway?', false)) {
            throw new RuntimeException('Aborted after migrate: data-loss check failed on: '.implode(', ', $emptied));
        }
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
