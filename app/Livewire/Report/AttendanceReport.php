<?php

namespace App\Livewire\Report;

use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use Livewire\Component;

class AttendanceReport extends Component
{
    public $fromDate;
    public $toDate;
    public $studentId = '';

    public $totalStudents;
    public $averageAttendanceDuration;
    public $studentsByGender = [];
    public $studentsBySchool = [];
    public $studentsByGrade = [];
    public $studentsByAge = [];

    public $dailyStatistics = [];
    public $hoursByStudent = [];
    public $attendanceLog = [];

    public function mount()
    {
        $this->fromDate = Carbon::now()->startOfWeek()->format('Y-m-d');
        $this->toDate = Carbon::now()->format('Y-m-d');
        $this->filter();
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
        ])->with(['student', 'student.schools' => fn ($q) => $q->where('is_current', true)->with('school'), 'student.grades' => fn ($q) => $q->where('is_current', true)->with('gradeTable'), 'attrs'])->get();

        $this->totalStudents = $attendances->count();
        $this->averageAttendanceDuration = $attendances->avg('total_time');
        $this->studentsByGender = $attendances->groupBy(fn ($a) => $a->student?->gender ? ucfirst(strtolower($a->student->gender)) : 'Unspecified')->map->count()->sortDesc();
        $this->studentsBySchool = $attendances->groupBy(fn ($a) => $a->student?->schools->first()?->school?->name ?: 'Unassigned')->map->count()->sortDesc();
        $this->studentsByGrade = $attendances->groupBy(fn ($a) => $a->student?->grades->first()?->gradeTable?->grade ?: 'Unassigned')->map->count()->sortDesc();

        $attendancesGroupedByDate = $attendances->groupBy(function ($attendance) {
            return Carbon::parse($attendance->date)->toDateString();
        })->sortKeys();

        $dailyStatistics = [];
        foreach ($attendancesGroupedByDate as $date => $attendancesForDate) {
            $dailyStatistics[$date] = [
                'totalStudents' => $attendancesForDate->count(),
                'averageAttendanceDuration' => $this->secondsToHms($attendancesForDate->avg('total_time')),
                'studentsByGender' => $attendancesForDate->groupBy(fn ($a) => $a->student?->gender ? ucfirst(strtolower($a->student->gender)) : 'Unspecified')->map->count(),
            ];
        }

        $this->dailyStatistics = $dailyStatistics;

        $this->hoursByStudent = $attendances->groupBy('student_id')
            ->map(function ($rows) {
                return [
                    'student' => $rows->first()->student,
                    'totalSeconds' => $rows->sum('total_time'),
                    'visits' => $rows->count(),
                ];
            })
            ->sortByDesc('totalSeconds')
            ->values();

        $this->attendanceLog = $this->studentId
            ? $attendances->where('student_id', $this->studentId)->sortByDesc('date')->values()
            : collect();
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
            'dailyStatistics' => $this->dailyStatistics,
            'hoursByStudent' => $this->hoursByStudent,
            'attendanceLog' => $this->attendanceLog,
            'students' => Student::active()->orderBy('name')->get(),
        ]);
    }
}
