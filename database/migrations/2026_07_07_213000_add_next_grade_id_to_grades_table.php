<?php

use App\Models\Grade;
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
        Schema::table('grades', function (Blueprint $table) {
            $table->foreignId('next_grade_id')->nullable()->after('grade')
                ->constrained('grades')->nullOnDelete();
        });

        // Pre-fill the unambiguous same-track sequences (GRADE 1->2->...->12,
        // FORM 1->2->3->4, CLASS 1->2->...->8, PP1->2->3). Cross-track transitions
        // (e.g. what follows PP3, or NOT YET IN SCHOOL/PLAYGROUP) depend on this
        // school's own curriculum and are left for an admin to configure via the
        // Settings > Grades screen rather than guessed here.
        foreach (['GRADE ', 'FORM ', 'CLASS ', 'PP'] as $prefix) {
            $grades = Grade::where('grade', 'like', $prefix.'%')
                ->get()
                ->filter(fn ($grade) => is_numeric(trim(str($grade->grade)->after($prefix))))
                ->sortBy(fn ($grade) => (int) trim(str($grade->grade)->after($prefix)))
                ->values();

            foreach ($grades as $index => $grade) {
                if ($next = $grades->get($index + 1)) {
                    $grade->update(['next_grade_id' => $next->id]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropConstrainedForeignId('next_grade_id');
        });
    }
};
