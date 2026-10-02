<?php

namespace App\Livewire\Dashboard;

use App\Models\Attendance;
use App\Models\AttendanceAttr;
use App\Models\Student;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class GreetingComponent extends Component
{
    public $name;

    public function mount($name)
    {
        $this->name = $name;
    }

    #[On('dashboard-changed')]
    public function refreshDashboard()
    {
    }

    function checkOut()
    {
        $attendance = Attendance::where('current_in', true)
            ->where('date', now()->toDateString())->get();

        foreach ($attendance as $att) {
            $attr = AttendanceAttr::where(['attendance_id' => $att->id])
                ->whereNull('time_out')->first();

            if ($attr) {
                $attr->update([
                    'time_out' => now(),
                ]);


                $att->update([
                    'current_in' => false,
                    'total_time' => $att->total_time + now()->diffInSeconds($attr->time_in, true),
                ]);
            }
        }

        $this->dispatch('dashboard-changed', ['type' => 'success', 'content' => 'Student checked out successfully']);
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
        $currentTime = now();

        $greeting = 'Good ';

        if ($currentTime->hour < 12) {
            $greeting .= 'Morning';
        } elseif ($currentTime->hour < 17) {
            $greeting .= 'Afternoon';
        } else {
            $greeting .= 'Evening';
        }

        // Counted in SQL rather than by hydrating every attendance row. The
        // greeting needs three numbers, and the student relation that used to
        // be eager-loaded alongside them was never read.
        $todayTotals = Attendance::query()
            ->whereBetween('date', [
                Carbon::now()->startOfDay(),
                Carbon::now()->endOfDay(),
            ])
            ->selectRaw('COUNT(*) as rows_count, AVG(total_time) as avg_time')
            ->first();

        $totalStudents = (int) $todayTotals->rows_count;
        $averageAttendanceDuration = $todayTotals->avg_time === null
            ? null
            : (float) $todayTotals->avg_time;

        $studentsInAttendanceToday = Attendance::where('date', now()->toDateString())
            ->where('current_in', true)
            ->count();

        return view('livewire.dashboard.greeting-component', [
            'greeting' => $greeting,
            'studentsInAttendanceToday' => $studentsInAttendanceToday,
            'totalStudents' => $totalStudents,
            'totalEnrolled' => Student::count(),
            'averageAttendanceDuration' => $this->secondsToHms($averageAttendanceDuration),
        ]);
    }
}
