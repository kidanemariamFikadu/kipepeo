<?php

use App\Livewire\Setup\Restore;
use App\Support\DatabaseBackup;

test('a database name that looks like a CLI option is refused', function (string $name) {
    // The restore archive supplies this name via its own .env, and the mysql
    // client parses options anywhere on its command line -- so without this
    // guard a name like --tee=<path> turns a database import into an
    // arbitrary file write under the web root.
    expect(fn () => DatabaseBackup::restoreUsing(
        sql: 'SELECT 1;',
        host: '127.0.0.1',
        port: '3306',
        database: $name,
        username: 'root',
        password: 'secret',
    ))->toThrow(RuntimeException::class, 'unsafe database name');
})->with([
    '--tee=/var/www/html/public/shell.php',
    '--defaults-extra-file=/tmp/evil.cnf',
    '--host=attacker.example.com',
    'db; DROP DATABASE other',
    'db name with spaces',
    '',
]);

test('dumping refuses an unsafe database name too', function () {
    expect(fn () => DatabaseBackup::dumpUsing(
        host: '127.0.0.1',
        port: '3306',
        database: '--tee=/tmp/pwned',
        username: 'root',
        password: 'secret',
    ))->toThrow(RuntimeException::class, 'unsafe database name');
});

test('an ordinary database name is accepted by the name guard', function () {
    // Reaching a connection error rather than the guard proves the name
    // itself passed validation.
    expect(fn () => DatabaseBackup::restoreUsing(
        sql: 'SELECT 1;',
        host: '203.0.113.1',
        port: '1',
        database: 'kipepeo_backup-1',
        username: 'root',
        password: 'secret',
    ))->not->toThrow(RuntimeException::class, 'unsafe database name');
});

test('restoring merges only the allow-listed env keys', function () {
    $current = <<<'ENV'
    APP_NAME=Kipepeo
    APP_ENV=production
    APP_KEY=base64:originalkey
    APP_DEBUG=false
    DB_HOST=127.0.0.1
    DB_DATABASE=kipepeo
    DB_PASSWORD=originalpassword
    MAIL_HOST=smtp.kipepeo.test
    ENV;

    $merged = Restore::mergeEnvString($current, [
        'DB_HOST' => 'db.internal',
        'DB_DATABASE' => 'restored_db',
        'DB_PASSWORD' => 'restoredpassword',
        'APP_KEY' => 'base64:restoredkey',
        // None of the following may cross over from an uploaded archive.
        'APP_DEBUG' => 'true',
        'APP_ENV' => 'local',
        'APP_NAME' => 'Pwned',
        'MAIL_HOST' => 'smtp.attacker.test',
        'QUEUE_CONNECTION' => 'sync',
    ]);

    expect($merged)->toContain('DB_HOST="db.internal"');
    expect($merged)->toContain('DB_DATABASE="restored_db"');
    expect($merged)->toContain('DB_PASSWORD="restoredpassword"');
    // Carried over so the source server's encrypted columns stay readable.
    expect($merged)->toContain('APP_KEY="base64:restoredkey"');

    expect($merged)->toContain('APP_DEBUG=false');
    expect($merged)->not->toContain('APP_DEBUG=true');
    expect($merged)->toContain('APP_ENV=production');
    expect($merged)->toContain('APP_NAME=Kipepeo');
    expect($merged)->toContain('MAIL_HOST=smtp.kipepeo.test');
    expect($merged)->not->toContain('attacker');
    expect($merged)->not->toContain('QUEUE_CONNECTION');
});

test('a newline inside an env value cannot smuggle in extra settings', function () {
    $merged = Restore::mergeEnvString(
        "APP_DEBUG=false\nDB_DATABASE=kipepeo\n",
        ['DB_DATABASE' => "restored\nAPP_DEBUG=true"],
    );

    // Asserted against the parsed result, not the raw text: the payload does
    // still appear verbatim inside the quoted DB_DATABASE value, and what
    // actually matters is that it stays a value and never becomes a setting.
    $parsed = Dotenv\Dotenv::parse($merged);

    expect($parsed['APP_DEBUG'])->toBe('false');
    expect($parsed['DB_DATABASE'])->toBe('restoredAPP_DEBUG=true');
});
