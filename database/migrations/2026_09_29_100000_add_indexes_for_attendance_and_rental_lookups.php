<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Attendance and rental rows accumulate for the life of the programme, but
 * the columns every screen filters on were unindexed -- so the dashboard,
 * the check-in screens and every attendance report ran full table scans.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Date-range filters on the reports and "today" on the dashboard.
            $table->index('date', 'attendances_date_index');
            // A single student's history (student detail, per-student report).
            $table->index(['student_id', 'date'], 'attendances_student_id_date_index');
        });

        Schema::table('rentals', function (Blueprint $table) {
            // Rental::overdue() -- whereNull(returned_at) + due_at < now.
            $table->index(['returned_at', 'due_at'], 'rentals_returned_at_due_at_index');
            // The circulation report's range filter and its ordering.
            $table->index('rented_at', 'rentals_rented_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex('attendances_date_index');
            $table->dropIndex('attendances_student_id_date_index');
        });

        Schema::table('rentals', function (Blueprint $table) {
            $table->dropIndex('rentals_returned_at_due_at_index');
            $table->dropIndex('rentals_rented_at_index');
        });
    }
};
