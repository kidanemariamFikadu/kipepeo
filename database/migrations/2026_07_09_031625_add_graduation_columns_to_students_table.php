<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->timestamp('graduated_at')->nullable()->after('gender');
            $table->foreignId('graduated_grade_id')->nullable()->after('graduated_at')
                ->constrained('grades')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('graduated_grade_id');
            $table->dropColumn('graduated_at');
        });
    }
};
