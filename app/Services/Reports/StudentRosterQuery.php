<?php

namespace App\Services\Reports;

use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;

/**
 * The filtered roster behind the students list, its select-all, and its
 * Excel export.
 *
 * All three read this one definition. The list previously restated the same
 * search/active/school query in two places; a third copy inside an export
 * would be the one nobody notices going stale, and the resulting file would
 * disagree with the screen it was downloaded from without saying so.
 */
class StudentRosterQuery
{
    /**
     * @param  array{search?:?string,school?:mixed,sortBy?:string,sortDir?:string}  $filters
     */
    public static function build(array $filters): Builder
    {
        return Student::search($filters['search'] ?? '')
            // The list shows the children currently attending, so graduated
            // students are out of scope here as well.
            ->active()
            ->when(
                ($filters['school'] ?? '') !== '',
                fn ($query) => $query->whereHas(
                    'schools',
                    fn ($q) => $q->where('school_id', $filters['school'])->where('is_current', true)
                )
            )
            ->orderBy($filters['sortBy'] ?? 'name', $filters['sortDir'] ?? 'ASC');
    }

    public static function filename(array $filters): string
    {
        $parts = ['student-registrations', now()->toDateString()];

        if (($filters['school'] ?? '') !== '') {
            $parts[] = 'filtered';
        }

        return implode('-', $parts).'.xlsx';
    }
}
