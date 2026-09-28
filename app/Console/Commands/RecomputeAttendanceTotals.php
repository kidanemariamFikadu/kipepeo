<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\VolunteerAttendance;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

/**
 * `total_time` is maintained incrementally on every check-out, so a bug in
 * that arithmetic leaves the stored value permanently wrong even after the
 * code is fixed. This recomputes it from the underlying check-in/check-out
 * pairs, which are the source of truth.
 *
 * Idempotent, and safe to re-run.
 */
class RecomputeAttendanceTotals extends Command
{
    protected $signature = 'attendance:recompute-totals {--dry-run : Report what would change without writing}';

    protected $description = 'Recompute attendance total_time from the recorded check-in/out pairs';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $studentRows = $this->reconcile(
            Attendance::query()->with('attrs'),
            'Student attendance',
            $dryRun,
        );

        $volunteerRows = $this->reconcile(
            VolunteerAttendance::query()->with('attrs'),
            'Volunteer attendance',
            $dryRun,
        );

        $total = $studentRows + $volunteerRows;

        $this->newLine();
        $this->info($dryRun
            ? "{$total} row(s) would be corrected. Re-run without --dry-run to apply."
            : "{$total} row(s) corrected.");

        return self::SUCCESS;
    }

    private function reconcile(Builder $query, string $label, bool $dryRun): int
    {
        $corrected = 0;

        $query->chunkById(200, function ($rows) use (&$corrected, $label, $dryRun) {
            foreach ($rows as $row) {
                // A row with an open check-in has time still accruing, so
                // there is no settled total to compare against yet.
                if ($row->attrs->isEmpty() || $row->attrs->whereNull('time_out')->isNotEmpty()) {
                    continue;
                }

                $expected = (int) round($row->attrs->sum(
                    fn ($attr) => \Carbon\Carbon::parse($attr->time_out)
                        ->diffInSeconds(\Carbon\Carbon::parse($attr->time_in), true)
                ));

                if ((int) $row->total_time === $expected) {
                    continue;
                }

                $this->line(sprintf(
                    '  %s #%d: %d -> %d',
                    $label, $row->id, (int) $row->total_time, $expected
                ));

                if (! $dryRun) {
                    $row->forceFill(['total_time' => $expected])->saveQuietly();
                }

                $corrected++;
            }
        });

        return $corrected;
    }
}
