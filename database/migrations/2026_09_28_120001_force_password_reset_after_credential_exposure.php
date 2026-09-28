<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Every user's bcrypt hash was recorded in the audit trail and rendered on
 * the user history screen, so any staff account could read them for as long
 * as those rows existed. The rows are scrubbed and the tokens cleared by the
 * preceding migration, but a hash that has been readable has to be treated
 * as compromised -- and a migration cannot choose new passwords.
 *
 * Flagging the accounts instead routes everyone through the app's existing
 * forced-reset screen on their next request, which sets a new password and
 * clears the flag.
 *
 * This is deliberately applied to every account: the audit rows that would
 * have identified exactly which users were affected are gone by this point,
 * so the safe assumption is all of them.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->update(['must_reset_password' => true]);
    }

    public function down(): void
    {
        // Deliberately not reversible: clearing the flag would hand accounts
        // back to passwords that are known to have been exposed.
    }
};
