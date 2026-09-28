<?php

namespace App\Console\Commands;

use App\Models\Grade;
use App\Models\GradeStudent;
use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * The grades table collates case-insensitively, so "GRADE 3" and "Grade 3"
 * are the same value to MySQL: any where('grade', ...)->first() picks one of
 * them arbitrarily. The settings form blocks duplicates, but rows created by
 * a seeder or import bypass that check.
 *
 * A duplicate is not cosmetic -- a second row typically has no next_grade_id,
 * so students holding it get graduated at year end instead of promoted.
 */
class MergeDuplicateGrades extends Command
{
    protected $signature = 'grades:merge-duplicates {--dry-run : Report what would change without writing}';

    protected $description = 'Merge grades whose names differ only by case or spacing';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $groups = Grade::orderBy('id')->get()
            ->groupBy(fn (Grade $grade) => mb_strtoupper(preg_replace('/\s+/', ' ', trim($grade->grade))))
            ->filter(fn ($group) => $group->count() > 1);

        if ($groups->isEmpty()) {
            $this->info('No duplicate grades found.');

            return self::SUCCESS;
        }

        $merged = 0;

        foreach ($groups as $name => $group) {
            // Keep the one that is wired into the promotion chain; fall back
            // to the oldest. Merging into a grade with no next_grade_id would
            // just move the graduate-instead-of-promote problem.
            $keep = $group->firstWhere(fn (Grade $g) => $g->next_grade_id !== null) ?? $group->first();
            $drop = $group->reject(fn (Grade $g) => $g->is($keep));

            $this->line("\"{$name}\" -> keeping #{$keep->id} (\"{$keep->grade}\")");

            foreach ($drop as $duplicate) {
                $students = GradeStudent::where('grade', $duplicate->id)->count();
                $this->line("  merging #{$duplicate->id} (\"{$duplicate->grade}\") — {$students} grade row(s)");

                if ($dryRun) {
                    $merged++;

                    continue;
                }

                DB::transaction(function () use ($duplicate, $keep) {
                    GradeStudent::where('grade', $duplicate->id)->update(['grade' => $keep->id]);
                    Grade::where('next_grade_id', $duplicate->id)->update(['next_grade_id' => $keep->id]);
                    Student::where('graduated_grade_id', $duplicate->id)->update(['graduated_grade_id' => $keep->id]);

                    $duplicate->forceDelete();
                });

                $merged++;
            }
        }

        $this->newLine();
        $this->info($dryRun
            ? "{$merged} duplicate grade(s) would be merged. Re-run without --dry-run to apply."
            : "{$merged} duplicate grade(s) merged.");

        return self::SUCCESS;
    }
}
