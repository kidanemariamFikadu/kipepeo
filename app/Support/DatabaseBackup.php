<?php

namespace App\Support;

use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Thin wrapper around the mysqldump/mysql CLI tools, independent of any
 * booted Laravel config -- restore needs to import against whatever
 * connection details are in a *freshly written* .env, not the config that
 * was cached when this request started.
 */
class DatabaseBackup
{
    public static function dump(): string
    {
        $connection = config('database.connections.'.config('database.default'));

        return self::dumpUsing(
            host: $connection['host'],
            port: (string) $connection['port'],
            database: $connection['database'],
            username: $connection['username'],
            password: $connection['password'],
        );
    }

    public static function dumpUsing(string $host, string $port, string $database, string $username, string $password): string
    {
        $binary = self::locateBinary('mysqldump');
        if ($binary === null) {
            throw new RuntimeException('mysqldump was not found on this server.');
        }

        $process = new Process([
            $binary,
            '--host='.$host,
            '--port='.$port,
            '--user='.$username,
            '--routines',
            '--single-transaction',
            $database,
        ]);
        $process->setTimeout(null);
        $process->run(null, ['MYSQL_PWD' => $password]);

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $sql = $process->getOutput();
        if (trim($sql) === '') {
            throw new RuntimeException('Database dump came back empty.');
        }

        return $sql;
    }

    public static function restoreUsing(string $sql, string $host, string $port, string $database, string $username, string $password): void
    {
        $binary = self::locateBinary('mysql');
        if ($binary === null) {
            throw new RuntimeException('The mysql client was not found on this server.');
        }

        $process = new Process([
            $binary,
            '--host='.$host,
            '--port='.$port,
            '--user='.$username,
            $database,
        ]);
        $process->setTimeout(null);
        $process->setInput($sql);
        $process->run(null, ['MYSQL_PWD' => $password]);

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    public static function locateBinary(string $name): ?string
    {
        if (self::binaryExists($name)) {
            return $name;
        }

        if (! windows_os()) {
            return null;
        }

        // mysqldump/mysql are frequently missing from PATH on Windows even
        // when installed, since the installer doesn't always add them.
        $home = getenv('USERPROFILE') ?: '';

        $patterns = [
            "C:\\Program Files\\MySQL\\MySQL Server *\\bin\\{$name}.exe",
            "C:\\Program Files\\MariaDB *\\bin\\{$name}.exe",
            "C:\\xampp\\mysql\\bin\\{$name}.exe",
            "C:\\laragon\\bin\\mysql\\*\\bin\\{$name}.exe",
            $home."\\.config\\herd\\bin\\{$name}.exe",
            $home."\\.config\\herd\\services\\mysql\\*\\bin\\{$name}.exe",
        ];

        foreach ($patterns as $pattern) {
            $matches = glob($pattern);
            if ($matches !== false && $matches !== []) {
                return $matches[0];
            }
        }

        return null;
    }

    private static function binaryExists(string $binary): bool
    {
        $command = windows_os() ? "where {$binary}" : "command -v {$binary}";

        return Process::fromShellCommandline($command)->run() === 0;
    }
}
