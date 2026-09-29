<?php

namespace App\Livewire\Report;

use App\Models\Attendance;
use App\Models\Grade;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceReport extends Component
{
    use WithPagination;

    /**
     * Every table on this page paginates independently (own position in its
     * own list) but shares one page-size control, matching the "paginate on
     * screen, print the full table" convention used elsewhere in this app.
     */
    private const PAGE_NAMES = ['daily-page', 'hours-student-page', 'hours-grade-page', 'consistency-page', 'log-page'];

    public $fromDate;
    public $toDate;
    public $studentId = '';
    public $gender = '';
    public $gradeId = '';
    public $perPage = 10;

    public $totalStudents;
    public $averageAttendanceDuration;
    public $studentsByGender = [];
    public $studentsBySchool = [];
    public $studentsByGrade = [];
    public $studentsByAge = [];

    public $dailyStatistics = [];
    public $hoursByStudent = [];
    public $hoursByGrade = [];
    public $attendanceConsistency = [];
    public $attendanceLog = [];

    public function mount()
    {
        $this->fromDate = Carbon::now()->startOfWeek()->format('Y-m-d');
        $this->toDate = Carbon::now()->format('Y-m-d');
        $this->filter();
    }

    public function updatedPerPage()
    {
        $this->resetAllPages();
    }

    private function resetAllPages()
    {
        foreach (self::PAGE_NAMES as $pageName) {
            $this->resetPage($pageName);
        }
    }

    /**
     * Manually paginates a plain in-memory collection (these tables are
     * derived from one big query up front, not separate Eloquent queries),
     * while still producing a fully Livewire-reactive paginator.
     *
     * Uses Paginator::resolveCurrentPage() rather than the trait's own
     * getPage() helper -- that's the exact call Eloquent's ->paginate()
     * makes internally, and it's the only thing that runs Livewire's
     * ensurePaginatorIsInitialized() for a given pageName, which registers
     * the property hook responsible for keeping this pageName's client-side
     * state in sync. Skipping it (i.e. just reading $this->paginators[$pageName]
     * directly) left pageNames other than the "official" one never
     * properly initialized, which produced a client-side sync bug on
     * gotoPage: https://flareapp.io/share/q5Yp3BX7 (PublicPropertyNotFoundException,
     * "Public property [$] not found").
     */
    private function paginate(Collection $collection, string $pageName): LengthAwarePaginator
    {
        $page = \Illuminate\Pagination\Paginator::resolveCurrentPage($pageName);
        $items = $collection->forPage($page, $this->perPage)->values();

        return new LengthAwarePaginator($items, $collection->count(), $this->perPage, $page, ['pageName' => $pageName]);
    }

    public function filter()
    {
        $this->validate([
            'fromDate' => 'required|date',
            'toDate' => 'required|date|after_or_equal:fromDate',
            'studentId' => 'nullable|exists:students,id',
            'gender' => 'nullable|in:male,female,other',
            'gradeId' => 'nullable|exists:grades,id',
        ]);

        // Everything below is aggregated in SQL and then assembled from
        // results bounded by days-in-range and distinct students. Loading
        // the attendance rows themselves cost ~3.4KB each, so a year of a
        // few hundred children ran to hundreds of megabytes per render.
        $totals = (clone $this->baseQuery())
            ->selectRaw('COUNT(*) as rows_count, AVG(total_time) as avg_time')
            ->first();

        $this->totalStudents = (int) $totals->rows_count;
        $this->averageAttendanceDuration = $totals->avg_time === null ? null : (float) $totals->avg_time;

        $perStudent = (clone $this->baseQuery())
            ->selectRaw('student_id, SUM(total_time) as total_seconds, COUNT(*) as visits, COUNT(DISTINCT date) as days_present')
            ->groupBy('student_id')
            ->get();

        // One lookup for the students actually involved, so school and grade
        // are resolved per student rather than by joining their history
        // tables -- a student carrying two is_current rows would otherwise
        // silently multiply every count on this page.
        $students = Student::with([
            'schools' => fn ($q) => $q->where('is_current', true)->with('school'),
            'grades' => fn ($q) => $q->where('is_current', true)->with('gradeTable'),
        ])->findMany($perStudent->pluck('student_id'))->keyBy('id');

        $genderOf = fn (?Student $s) => $s?->gender ? ucfirst(strtolower($s->gender)) : null;
        $schoolOf = fn (?Student $s) => $s?->schools->first()?->school?->name ?: 'Unassigned';
        $gradeOf = fn (?Student $s) => $s?->grades->first()?->gradeTable?->grade;

        $rowsFor = fn ($studentId) => $students->get($studentId);

        // Attendance counts per student, folded up by the student's current
        // school/grade/gender -- identical to grouping the raw rows, without
        // holding them.
        $this->studentsByGender = $perStudent
            ->groupBy(fn ($r) => $genderOf($rowsFor($r->student_id)) ?: 'Unspecified')
            ->map(fn ($rows) => (int) $rows->sum('visits'))
            ->sortDesc();

        $this->studentsBySchool = $perStudent
            ->groupBy(fn ($r) => $schoolOf($rowsFor($r->student_id)))
            ->map(fn ($rows) => (int) $rows->sum('visits'))
            ->sortDesc();

        $this->studentsByGrade = $perStudent
            ->groupBy(fn ($r) => $gradeOf($rowsFor($r->student_id)) ?: 'Unassigned')
            ->map(fn ($rows) => (int) $rows->sum('visits'))
            ->sortDesc();

        $dailyTotals = (clone $this->baseQuery())
            ->selectRaw('date, COUNT(*) as rows_count, AVG(total_time) as avg_time')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // students is a belongsTo, so joining it cannot multiply rows.
        $dailyGender = (clone $this->baseQuery())
            ->join('students', 'students.id', '=', 'attendances.student_id')
            ->selectRaw('attendances.date as date, students.gender as gender, COUNT(*) as rows_count')
            ->groupBy('attendances.date', 'students.gender')
            ->get()
            ->groupBy(fn ($r) => Carbon::parse($r->date)->toDateString());

        $this->dailyStatistics = $dailyTotals->map(function ($day) use ($dailyGender) {
            $date = Carbon::parse($day->date)->toDateString();

            return [
                'date' => $date,
                'totalStudents' => (int) $day->rows_count,
                'averageAttendanceDuration' => $this->secondsToHms($day->avg_time),
                'studentsByGender' => ($dailyGender->get($date) ?? collect())
                    ->groupBy(fn ($r) => $r->gender ? ucfirst(strtolower($r->gender)) : 'Unspecified')
                    ->map(fn ($rows) => (int) $rows->sum('rows_count')),
            ];
        })->values();

        // Only the primitive fields the table actually displays are kept
        // here (not the Student model itself) - these collections can run
        // into the hundreds of rows, and a wire payload full of nested
        // Eloquent models is both unnecessarily large and, per Livewire's
        // own pagination docs, outside the well-trodden path for a
        // component with several independent paginators on one page.
        $this->hoursByStudent = $perStudent
            ->map(function ($row) use ($rowsFor, $genderOf) {
                $student = $rowsFor($row->student_id);

                return [
                    'studentId' => (int) $row->student_id,
                    'studentName' => $student?->name,
                    'studentGender' => $genderOf($student),
                    'totalSeconds' => (int) $row->total_seconds,
                    'visits' => (int) $row->visits,
                ];
            })
            ->sortByDesc('totalSeconds')
            ->values();

        $this->hoursByGrade = $perStudent
            ->groupBy(fn ($r) => $gradeOf($rowsFor($r->student_id)) ?: 'Unassigned')
            ->map(fn ($rows, $grade) => [
                'grade' => $grade,
                'totalSeconds' => (int) $rows->sum('total_seconds'),
                'students' => $rows->pluck('student_id')->unique()->count(),
            ])
            ->sortByDesc('totalSeconds')
            ->values();

        // Unique students, bucketed by age, so the range reflects who is
        // actually using the space rather than being skewed by how often
        // any one child attended.
        $this->studentsByAge = $students
            ->groupBy(fn (Student $student) => $this->ageBucket($student->student_age))
            ->map->count()
            ->sortKeys();

        $weekdaysInRange = $this->countWeekdays(Carbon::parse($this->fromDate), Carbon::parse($this->toDate));

        $this->attendanceConsistency = $perStudent
            ->map(function ($row) use ($rowsFor, $genderOf, $gradeOf, $weekdaysInRange) {
                $student = $rowsFor($row->student_id);
                $daysPresent = (int) $row->days_present;

                return [
                    'studentId' => (int) $row->student_id,
                    'studentName' => $student?->name,
                    'studentGender' => $genderOf($student),
                    'studentGrade' => $gradeOf($student),
                    'daysPresent' => $daysPresent,
                    'totalSeconds' => (int) $row->total_seconds,
                    'consistency' => $weekdaysInRange > 0 ? round(($daysPresent / $weekdaysInRange) * 100) : 0,
                ];
            })
            ->sortByDesc('consistency')
            ->values()
            // Embed the rank so the "Top 5 most consistent" badge survives
            // pagination - each page only sees its own slice, not the whole
            // sorted list, so a local loop index can't be used for this.
            ->map(function ($row, $index) {
                $row['rank'] = $index + 1;

                return $row;
            });

        // Row-level by nature, but only ever built for a single student.
        $this->attendanceLog = $this->studentId
            ? (clone $this->baseQuery())->with('attrs')->orderByDesc('date')->get()
            : collect();

        $this->resetAllPages();
    }

    /**
     * The filtered set of attendance rows every figure on this page derives
     * from. Returned as a builder so each aggregate runs in SQL instead of
     * hydrating the rows.
     */
    private function baseQuery()
    {
        return Attendance::query()
            ->whereBetween('date', [
                Carbon::parse($this->fromDate)->toDateString(),
                Carbon::parse($this->toDate)->toDateString(),
            ])
            // Soft-deleted students keep their attendance rows, which
            // otherwise still count towards every total and chart on this
            // page under a blank name.
            ->whereHas('student')
            // Scoping here (not just the log at the bottom) means every card
            // and chart above reflects the selected student instead of the
            // whole cohort once one is picked.
            ->when($this->studentId, fn ($q) => $q->where('attendances.student_id', $this->studentId))
            ->when($this->gender, fn ($q) => $q->whereHas('student', fn ($sq) => $sq->where('gender', $this->gender)))
            ->when($this->gradeId, fn ($q) => $q->whereHas('student.grades', fn ($sq) => $sq->where('is_current', true)->where('grade', $this->gradeId)));
    }

    /**
     * Buckets ages into ranges meaningful for a children's learning space
     * rather than showing every single-year age as its own bar.
     */
    function ageBucket($age)
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

    function countWeekdays(Carbon $from, Carbon $to)
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

    function secondsToHms($seconds)
    {
        $seconds = (int) round($seconds ?? 0);
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }


    public function render()
    {

        return view('livewire.report.attendance-report', [
            'totalStudents' => $this->totalStudents,
            'timeFormatted' => $this->secondsToHms($this->averageAttendanceDuration),
            'averageAttendanceDuration' => $this->averageAttendanceDuration,
            'studentsByGender' => $this->studentsByGender,
            'studentsBySchool' => $this->studentsBySchool,
            'studentsByGrade' => $this->studentsByGrade,
            'studentsByAge' => $this->studentsByAge,
            // Full collections - used for the print-only tables so the
            // printed page always has every row, regardless of what page
            // is showing on screen.
            'dailyStatistics' => $this->dailyStatistics,
            'hoursByStudent' => $this->hoursByStudent,
            'hoursByGrade' => $this->hoursByGrade,
            'attendanceConsistency' => $this->attendanceConsistency,
            'attendanceLog' => $this->attendanceLog,
            // Paginated - used for the on-screen tables.
            'dailyStatisticsPage' => $this->paginate($this->dailyStatistics, 'daily-page'),
            'hoursByStudentPage' => $this->paginate($this->hoursByStudent, 'hours-student-page'),
            'hoursByGradePage' => $this->paginate($this->hoursByGrade, 'hours-grade-page'),
            'attendanceConsistencyPage' => $this->paginate($this->attendanceConsistency, 'consistency-page'),
            'attendanceLogPage' => $this->paginate($this->attendanceLog, 'log-page'),
            'students' => Student::active()->orderBy('name')->get(),
            'grades' => Grade::orderBy('grade')->get(),
        ]);
    }
}
