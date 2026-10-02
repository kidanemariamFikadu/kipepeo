<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * The handful of pure metric definitions that more than one report depends
 * on. They live here so the attendance screen and the narrative snapshot
 * cannot drift apart on what "9-11" or "a weekday" means -- a disagreement
 * that would surface as a generated report citing a figure the screen
 * beside it contradicts.
 */
class ReportMetrics
{
    /**
     * Buckets ages into ranges meaningful for a children's learning space
     * rather than showing every single-year age as its own bar.
     */
    public static function ageBucket(?int $age): string
    {
        if (is_null($age)) {
            return 'Unknown';
        }

        return match (true) {
            $age <= 5 => 'Under 6',
            $age <= 8 => '6-8',
            $age <= 11 => '9-11',
            $age <= 14 => '12-14',
            $age <= 17 => '15-17',
            default => '18+',
        };
    }

    /**
     * Weekdays in an inclusive date range. This is the denominator for
     * attendance consistency: the centre runs on weekdays, so counting
     * calendar days would make every child look half as regular as they are.
     */
    public static function countWeekdays(Carbon $from, Carbon $to): int
    {
        $count = 0;
        $cursor = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();

        while ($cursor->lte($end)) {
            if (! $cursor->isWeekend()) {
                $count++;
            }
            $cursor->addDay();
        }

        return $count;
    }
}
