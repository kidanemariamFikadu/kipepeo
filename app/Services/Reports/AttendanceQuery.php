<?php

namespace App\Services\Reports;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * The filtered set of attendance rows behind the attendance analytics
 * screen, and behind its Excel export.
 *
 * Both read this one definition on purpose. An export that quietly applied
 * its filters differently from the page it was downloaded from would hand
 * someone a spreadsheet that disagrees with the screen they were looking at,
 * and nothing about the file would reveal it.
 */
class AttendanceQuery
{
    /**
     * @param  array{fromDate?:?string,toDate?:?string,studentId?:mixed,gender?:?string,gradeId?:mixed}  $filters
     */
    public static function build(array $filters): Builder
    {
        $from = Carbon::parse($filters['fromDate'])->toDateString();
        $to = Carbon::parse($filters['toDate'])->toDateString();

        return Attendance::query()
            ->whereBetween('date', [$from, $to])
            // Soft-deleted students keep their attendance rows, which would
            // otherwise still count towards every total under a blank name.
            ->whereHas('student')
            ->when($filters['studentId'] ?? null, fn ($q, $id) => $q->where('attendances.student_id', $id))
            ->when(
                $filters['gender'] ?? null,
                fn ($q, $gender) => $q->whereHas('student', fn ($sq) => $sq->where('gender', $gender))
            )
            ->when(
                $filters['gradeId'] ?? null,
                fn ($q, $gradeId) => $q->whereHas(
                    'student.grades',
                    fn ($sq) => $sq->where('is_current', true)->where('grade', $gradeId)
                )
            );
    }

    /**
     * A filename that says what is inside it, so a folder of these stays
     * readable months later.
     */
    public static function filename(array $filters): string
    {
        $from = Carbon::parse($filters['fromDate'])->toDateString();
        $to = Carbon::parse($filters['toDate'])->toDateString();

        $parts = ['attendance', $from, 'to', $to];

        if ($filters['gender'] ?? null) {
            $parts[] = strtolower($filters['gender']);
        }

        return implode('-', $parts).'.xlsx';
    }
}
