<?php

namespace App\Livewire\Report;

use App\Models\Attendance;
use Carbon\Carbon;
use Livewire\Component;

class AttendanceReport extends Component
{
    public $fromDate;
    public $toDate;

    public $totalStudents;
    public $averageAttendanceDuration;
    public $studentsByGender = [];
    public $studentsBySchool = [];
    public $studentsByGrade = [];
    public $studentsByAge = [];

    public $dailyStatistics = [];

    public function mount()
    {
        $this->fromDate = Carbon::now()->format('Y-m-d');
        $this->toDate = Carbon::now()->format('Y-m-d');
    }

    public function filter()
    {
        $query = Attendance::query();

        if ($this->fromDate && $this->toDate) {
            $query->whereBetween('date', [
                Carbon::parse($this->fromDate)->startOfDay(),
                Carbon::parse($this->toDate)->endOfDay()
            ]);
        }

        $attendances = $query->with(['student'])->get();

        $this->totalStudents = $attendances->count();
        $this->averageAttendanceDuration = $attendances->avg('total_time');
        $this->studentsByGender = $attendances->groupBy('student.gender')->map->count();
        // $this->studentsByAge = $attendances->groupBy('student.studentAge')->map->avg();
        $this->studentsBySchool = $attendances->groupBy('student.currentSchool.name')->map->count();
        $this->studentsByGrade = $attendances->groupBy('student.currentGrade.grade')->map->count();

        $attendancesGroupedByDate = $attendances->groupBy(function ($attendance) {
            return Carbon::parse($attendance->date)->toDateString();
        });

        foreach ($attendancesGroupedByDate as $date => $attendancesForDate) {
            $dailyStatistics[$date] = [
                'totalStudents' => $attendancesForDate->count(),
                'averageAttendanceDuration' => $this->secondsToHms($attendancesForDate->avg('total_time')),
                'studentsByGender' => $attendancesForDate->groupBy('student.gender')->map->count()
            ];
        }

        $this->dailyStatistics = $dailyStatistics;
    }

    function secondsToHms($seconds)
    {
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
        ]);
    }
}
