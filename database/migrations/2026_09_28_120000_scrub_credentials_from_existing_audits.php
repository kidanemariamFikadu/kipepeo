<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Audit rows written before User::$auditExclude existed captured the full
 * attribute set on every write -- including the bcrypt hash, the
 * remember_token and the two-factor columns. The user history screen
 * rendered those values, so anyone who could open it could read a live
 * remember_token and authenticate as that account.
 *
 * The code no longer records them, but rows already on disk still hold
 * them. This strips those keys in place, leaving the rest of each row's
 * history (name, email, role changes) intact.
 */
return new class extends Migration
{
    private const CREDENTIAL_KEYS = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    public function up(): void
    {
        DB::table('audits')
            ->orderBy('id')
            ->chunkById(200, function ($audits) {
                foreach ($audits as $audit) {
                    $changes = [];

                    foreach (['old_values', 'new_values'] as $column) {
                        $decoded = json_decode($audit->{$column} ?? '', true);

                        if (! is_array($decoded)) {
                            continue;
                        }

                        $scrubbed = array_diff_key($decoded, array_flip(self::CREDENTIAL_KEYS));

                        if (count($scrubbed) !== count($decoded)) {
                            $changes[$column] = json_encode($scrubbed);
                        }
                    }

                    if ($changes !== []) {
                        DB::table('audits')->where('id', $audit->id)->update($changes);
                    }
                }
            });

        // A token that has been readable by every staff account is no longer
        // a secret. Nulling it only forces a fresh login on devices using
        // "remember me"; passwords cannot be rotated from here and must be
        // reset out of band.
        DB::table('users')->whereNotNull('remember_token')->update(['remember_token' => null]);
    }

    public function down(): void
    {
        // Deliberately irreversible: the point of this migration is that the
        // values are gone, and they should not come back.
    }
};
