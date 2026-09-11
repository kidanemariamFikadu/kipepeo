<?php

namespace App\Livewire\Report;

use App\Models\Attendance;
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
    private const PAGE_NAMES = ['daily-page', 'hours-student-page', 'hours-grade-page', 'girls-page', 'log-page'];

    public $fromDate;
    public $toDate;
    public $studentId = '';
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
    public $girlsAttendance = [];
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
        ]);

        $attendances = Attendance::whereBetween('date', [
            Carbon::parse($this->fromDate)->startOfDay(),
            Carbon::parse($this->toDate)->endOfDay(),
        ])
            // Scoping here (not just the log at the bottom) means every card
            // and chart above reflects the selected student instead of the
            // whole cohort once one is picked.
            ->when($this->studentId, fn ($q) => $q->where('student_id', $this->studentId))
            ->with(['student', 'student.schools' => fn ($q) => $q->where('is_current', true)->with('school'), 'student.grades' => fn ($q) => $q->where('is_current', true)->with('gradeTable'), 'attrs'])
            ->get();

        $this->totalStudents = $attendances->count();
        $this->averageAttendanceDuration = $attendances->avg('total_time');
        $this->studentsByGender = $attendances->groupBy(fn ($a) => $a->student?->gender ? ucfirst(strtolower($a->student->gender)) : 'Unspecified')->map->count()->sortDesc();
        $this->studentsBySchool = $attendances->groupBy(fn ($a) => $a->student?->schools->first()?->school?->name ?: 'Unassigned')->map->count()->sortDesc();
        $this->studentsByGrade = $attendances->groupBy(fn ($a) => $a->student?->grades->first()?->gradeTable?->grade ?: 'Unassigned')->map->count()->sortDesc();

        $attendancesGroupedByDate = $attendances->groupBy(function ($attendance) {
            return Carbon::parse($attendance->date)->toDateString();
        })->sortKeys();

        $dailyStatistics = collect();
        foreach ($attendancesGroupedByDate as $date => $attendancesForDate) {
            $dailyStatistics->push([
                'date' => $date,
                'totalStudents' => $attendancesForDate->count(),
                'averageAttendanceDuration' => $this->secondsToHms($attendancesForDate->avg('total_time')),
                'studentsByGender' => $attendancesForDate->groupBy(fn ($a) => $a->student?->gender ? ucfirst(strtolower($a->student->gender)) : 'Unspecified')->map->count(),
            ]);
        }

        $this->dailyStatistics = $dailyStatistics;

        // Only the primitive fields the table actually displays are kept
        // here (not the Student model itself) - these collections can run
        // into the hundreds of rows, and a wire payload full of nested
        // Eloquent models is both unnecessarily large and, per Livewire's
        // own pagination docs, outside the well-trodden path for a
        // component with several independent paginators on one page.
        $this->hoursByStudent = $attendances->groupBy('student_id')
            ->map(function ($rows, $studentId) {
                $student = $rows->first()->student;

                return [
                    'studentId' => (int) $studentId,
                    'studentName' => $student?->name,
                    'studentGender' => $student?->gender ? ucfirst(strtolower($student->gender)) : null,
                    'totalSeconds' => $rows->sum('total_time'),
                    'visits' => $rows->count(),
                ];
            })
            ->sortByDesc('totalSeconds')
            ->values();

        $this->hoursByGrade = $attendances->groupBy(fn ($a) => $a->student?->grades->first()?->gradeTable?->grade ?: 'Unassigned')
            ->map(function ($rows, $grade) {
                return [
                    'grade' => $grade,
                    'totalSeconds' => $rows->sum('total_time'),
                    'students' => $rows->pluck('student_id')->unique()->count(),
                ];
            })
            ->sortByDesc('totalSeconds')
            ->values();

        // Unique students, bucketed by age, so the range reflects who is
        // actually using the space rather than being skewed by how often
        // any one child attended.
        $this->studentsByAge = $attendances->pluck('student')->filter()->unique('id')
            ->groupBy(fn ($student) => $this->ageBucket($student->student_age))
            ->map->count()
            ->sortKeys();

        $weekdaysInRange = $this->countWeekdays(Carbon::parse($this->fromDate), Carbon::parse($this->toDate));

        $this->girlsAttendance = $attendances
            ->filter(fn ($a) => $a->student && strtolower($a->student->gender ?? '') === 'female')
            ->groupBy('student_id')
            ->map(function ($rows, $studentId) use ($weekdaysInRange) {
                $student = $rows->first()->student;
                $daysPresent = $rows->pluck('date')->unique()->count();

                return [
                    'studentId' => (int) $studentId,
                    'studentName' => $student?->name,
                    'studentGrade' => $student?->grades->first()?->gradeTable?->grade,
                    'daysPresent' => $daysPresent,
                    'totalSeconds' => $rows->sum('total_time'),
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

        $this->attendanceLog = $this->studentId
            ? $attendances->sortByDesc('date')->values()
            : collect();

        $this->resetAllPages();
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
            'girlsAttendance' => $this->girlsAttendance,
            'attendanceLog' => $this->attendanceLog,
            // Paginated - used for the on-screen tables.
            'dailyStatisticsPage' => $this->paginate($this->dailyStatistics, 'daily-page'),
            'hoursByStudentPage' => $this->paginate($this->hoursByStudent, 'hours-student-page'),
            'hoursByGradePage' => $this->paginate($this->hoursByGrade, 'hours-grade-page'),
            'girlsAttendancePage' => $this->paginate($this->girlsAttendance, 'girls-page'),
            'attendanceLogPage' => $this->paginate($this->attendanceLog, 'log-page'),
            'students' => Student::active()->orderBy('name')->get(),
        ]);
    }
}
