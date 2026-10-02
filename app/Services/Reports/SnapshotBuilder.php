<?php

namespace App\Services\Reports;

use App\Models\Attendance;
use App\Models\Student;
use App\Support\ReportMetrics;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds the aggregates-only snapshot that a narrative report is generated
 * from.
 *
 * Nothing that identifies a child may appear in the result: no names, no
 * dates of birth, no guardian contacts. Every value is a count, an average,
 * or a label describing a group of children -- never a child. That is a
 * hard boundary rather than a convention, because this array is the payload
 * that leaves the server, and SnapshotPrivacyTest fails the build if a name
 * or a phone number ever reaches it.
 */
class SnapshotBuilder
{
    public const SCHEMA_VERSION = 1;

    /**
     * Categories smaller than this collapse into a single "fewer than N"
     * bucket. A named school beside the count 3 can identify a specific
     * child in a small community, which defeats the point of sending
     * aggregates. Totals are preserved: the small categories are merged,
     * not dropped.
     */
    public function __construct(private readonly int $suppressBelow = 5) {}

    /**
     * Suppression is for anything leaving the building. Internal staff
     * reports read the same figures unsuppressed.
     */
    public static function unsuppressed(): self
    {
        return new self(suppressBelow: 0);
    }

    public function build(Carbon $from, Carbon $to, bool $withComparison = true): array
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->startOfDay();

        $snapshot = [
            'schema_version' => self::SCHEMA_VERSION,
            'generated_at' => now()->toIso8601String(),
            'period' => $this->period($from, $to),
            'attendance' => $this->attendance($from, $to),
            'enrollment' => $this->enrollment($from, $to),
            'caveats' => $this->caveats(),
        ];

        if ($withComparison) {
            // The immediately preceding window of equal length. Without it a
            // report can only describe what it was handed; it cannot say
            // that anything changed.
            $length = $from->diffInDays($to);
            $priorTo = $from->copy()->subDay();
            $priorFrom = $priorTo->copy()->subDays($length);

            $snapshot['previous_period'] = [
                'period' => $this->period($priorFrom, $priorTo),
                'attendance' => $this->attendance($priorFrom, $priorTo, withDaily: false),
                'enrollment' => $this->enrollment($priorFrom, $priorTo),
            ];
        }

        return $snapshot;
    }

    private function period(Carbon $from, Carbon $to): array
    {
        return [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'days' => $from->diffInDays($to) + 1,
            'weekdays' => ReportMetrics::countWeekdays($from, $to),
        ];
    }

    private function attendance(Carbon $from, Carbon $to, bool $withDaily = true): array
    {
        $base = fn () => Attendance::query()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            // Soft-deleted students keep their attendance rows, which would
            // otherwise count towards every total under a blank name. The
            // attendance screen excludes them the same way.
            ->whereHas('student');

        $totals = $base()
            ->selectRaw('COUNT(*) as visits, COUNT(DISTINCT student_id) as unique_students, AVG(total_time) as avg_time')
            ->first();

        $perStudent = $base()
            ->selectRaw('student_id, SUM(total_time) as total_seconds, COUNT(*) as visits, COUNT(DISTINCT date) as days_present')
            ->groupBy('student_id')
            ->get();

        // One lookup for the students actually involved, so school and grade
        // resolve per student rather than through a join -- a student
        // carrying two is_current rows would otherwise multiply every count.
        $students = Student::with([
            'schools' => fn ($q) => $q->where('is_current', true)->with('school'),
            'grades' => fn ($q) => $q->where('is_current', true)->with('gradeTable'),
        ])->findMany($perStudent->pluck('student_id'))->keyBy('id');

        // Grouped once, then read twice: the visit total is what gets
        // reported, but how many *children* are behind that total is what
        // decides whether the category is safe to name. One child attending
        // twenty times is a disclosure; twenty children attending once is
        // not, and both read as "20" if you only look at the metric.
        $groupedBy = fn (callable $label) => $perStudent->groupBy(fn ($row) => $label($students->get($row->student_id)));
        $visits = fn (Collection $groups) => $groups->map(fn ($rows) => (int) $rows->sum('visits'))->sortDesc();
        $children = fn (Collection $groups) => $groups->map(fn ($rows) => $rows->pluck('student_id')->unique()->count());

        $schoolGroups = $groupedBy(fn (?Student $s) => $s?->schools->first()?->school?->name ?: 'Unassigned');
        $gradeGroups = $groupedBy(fn (?Student $s) => $s?->grades->first()?->gradeTable?->grade ?: 'Unassigned');
        $genderGroups = $groupedBy(fn (?Student $s) => $this->genderOf($s));

        $bySchool = $visits($schoolGroups);
        $byGrade = $visits($gradeGroups);

        $weekdays = ReportMetrics::countWeekdays($from, $to);
        $consistency = $perStudent->map(
            fn ($row) => $weekdays > 0 ? round(((int) $row->days_present / $weekdays) * 100) : 0
        )->sort()->values();

        $attendance = [
            'visits' => (int) $totals->visits,
            'unique_students' => (int) $totals->unique_students,
            'average_visit_seconds' => $totals->avg_time === null ? null : (int) round($totals->avg_time),
            'average_visit_minutes' => $totals->avg_time === null ? null : (int) round($totals->avg_time / 60),
            'visits_by_gender' => $this->suppress($visits($genderGroups)->all(), $children($genderGroups)->all()),
            'visits_by_grade' => $this->suppress($byGrade->all(), $children($gradeGroups)->all()),
            'visits_by_school' => $this->suppress($bySchool->all(), $children($schoolGroups)->all()),
            // The children are drawn from many different schools, so after
            // suppression the breakdown above is usually one "Other" bucket.
            // These counts survive suppression and carry the real signal:
            // "children came from 32 schools" is both safe and the more
            // interesting fact.
            'distinct_schools_attending' => $this->countNamed($bySchool),
            'distinct_grades_attending' => $this->countNamed($byGrade),
            'unique_students_by_age_band' => $this->suppress(
                $students
                    ->groupBy(fn (Student $s) => ReportMetrics::ageBucket($s->student_age))
                    ->map->count()
                    ->sortKeys()
                    ->all()
            ),
            // A distribution rather than a ranked list of children: the shape
            // of how regularly children attend is what a report needs, and
            // naming the most and least regular child is not.
            'consistency_percent' => [
                'median' => $this->median($consistency),
                'students_at_or_above_80' => $consistency->filter(fn ($c) => $c >= 80)->count(),
                'students_below_40' => $consistency->filter(fn ($c) => $c < 40)->count(),
            ],
        ];

        if ($withDaily) {
            $attendance['daily'] = $base()
                ->selectRaw('date, COUNT(*) as visits, AVG(total_time) as avg_time')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn ($day) => [
                    'date' => Carbon::parse($day->date)->toDateString(),
                    'visits' => (int) $day->visits,
                    'average_visit_minutes' => $day->avg_time === null ? null : (int) round($day->avg_time / 60),
                ])
                ->values()
                ->all();
        }

        return $attendance;
    }

    /**
     * The roster as it stood at the end of the period, reconstructed from
     * created_at and graduated_at so the comparison window means something.
     */
    private function enrollment(Carbon $from, Carbon $to): array
    {
        $start = $from->copy()->startOfDay();
        $end = $to->copy()->endOfDay();

        $activeAtEnd = Student::query()
            ->where('created_at', '<=', $end)
            ->where(fn ($q) => $q->whereNull('graduated_at')->orWhere('graduated_at', '>', $end))
            ->with([
                'schools' => fn ($q) => $q->where('is_current', true)->with('school'),
                'grades' => fn ($q) => $q->where('is_current', true)->with('gradeTable'),
            ])
            ->get();

        // Student records carry the date they were created in this system,
        // which for a bulk import is the import date rather than the date a
        // child joined. For a period before that import this reconstruction
        // finds nobody -- and "0 children" next to "39 children" reads as
        // enrollment growing from zero, which is a sentence no report should
        // be able to write. Say nothing instead.
        if ($activeAtEnd->isEmpty() && Student::query()->exists()) {
            return [
                'unavailable' => 'No student record in this system predates this period, so the roster for it cannot be reconstructed. This is a limit of the records, not a count of zero.',
            ];
        }

        $bySchool = $activeAtEnd->groupBy(fn (Student $s) => $s->schools->first()?->school?->name ?: 'Unassigned')->map->count()->sortDesc();
        $byGrade = $activeAtEnd->groupBy(fn (Student $s) => $s->grades->first()?->gradeTable?->grade ?: 'Unassigned')->map->count()->sortDesc();

        return [
            'total_active' => $activeAtEnd->count(),
            'first_recorded_in_period' => Student::whereBetween('created_at', [$start, $end])->count(),
            'graduated_in_period' => Student::whereBetween('graduated_at', [$start, $end])->count(),
            'distinct_schools' => $this->countNamed($bySchool),
            'distinct_grades' => $this->countNamed($byGrade),
            'by_gender' => $this->suppress(
                $activeAtEnd->groupBy(fn (Student $s) => $this->genderOf($s))->map->count()->sortDesc()->all()
            ),
            'by_grade' => $this->suppress($byGrade->all()),
            'by_school' => $this->suppress($bySchool->all()),
        ];
    }

    /**
     * How many real categories a breakdown had, ignoring the catch-all. Safe
     * to report even when every category was too small to name.
     */
    private function countNamed(Collection $counts): int
    {
        return $counts->keys()->reject(fn ($key) => $key === 'Unassigned')->count();
    }

    private function genderOf(?Student $student): string
    {
        return $student?->gender ? ucfirst(strtolower($student->gender)) : 'Unspecified';
    }

    private function median(Collection $sorted): ?float
    {
        if ($sorted->isEmpty()) {
            return null;
        }

        $count = $sorted->count();
        $middle = (int) floor($count / 2);

        return $count % 2
            ? (float) $sorted[$middle]
            : (float) (($sorted[$middle - 1] + $sorted[$middle]) / 2);
    }

    /**
     * Collapses categories representing too few children into one bucket,
     * keeping the total intact so the figures in a report still add up.
     *
     * $childCounts is how many children sit behind each reported value, and
     * is what the threshold is judged against. Where the reported value is
     * itself a headcount the two are the same array; where it is a visit
     * total they are not, and using the value would let a single child's
     * twenty visits pass as a safely large number.
     */
    private function suppress(array $values, ?array $childCounts = null): array
    {
        if ($this->suppressBelow < 1) {
            return $values;
        }

        $childCounts ??= $values;

        $kept = [];
        $merged = 0;
        $mergedCategories = 0;

        foreach ($values as $label => $value) {
            if (($childCounts[$label] ?? 0) < $this->suppressBelow) {
                $merged += $value;
                $mergedCategories++;

                continue;
            }

            $kept[$label] = $value;
        }

        if ($mergedCategories > 0) {
            $kept[sprintf(
                'Other (%d %s with fewer than %d children)',
                $mergedCategories,
                $mergedCategories === 1 ? 'category' : 'categories',
                $this->suppressBelow,
            )] = $merged;
        }

        return $kept;
    }

    /**
     * Shipped inside the snapshot so the prompt can carry them: the model
     * should not claim a child was in Grade 3 "during March" when what the
     * data actually records is where that child is placed today.
     */
    private function caveats(): array
    {
        return [
            'Grade and school breakdowns reflect current placement, not placement during the period.',
            'A "visit" is one attendance record for one child on one day; a child attending ten days counts as ten visits.',
            'Attendance consistency is measured against weekdays in the period, not calendar days.',
            'The date a child was first recorded in this system may be an import date rather than the date they joined.',
            'Categories with fewer children than the suppression threshold are merged into an "Other" bucket, so named categories may not list every child.',
        ];
    }
}
