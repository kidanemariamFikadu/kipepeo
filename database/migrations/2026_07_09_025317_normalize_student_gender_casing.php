<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The gender column has accumulated mixed casing ("Male"/"male",
     * "Female"/"female") from inconsistent form/import code, which made
     * reports and charts double-count by treating each casing as a
     * separate group. Normalize to lowercase, matching the app's
     * validation convention (in:male,female,other).
     */
    public function up(): void
    {
        DB::table('students')->whereNotNull('gender')->update([
            'gender' => DB::raw('LOWER(TRIM(gender))'),
        ]);
    }

    public function down(): void
    {
        // Casing isn't recoverable once normalized; nothing to reverse.
    }
};
