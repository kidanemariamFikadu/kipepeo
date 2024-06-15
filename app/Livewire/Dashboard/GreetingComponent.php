<?php

namespace App\Livewire\Dashboard;

use App\Models\Attendance;
use App\Models\AttendanceAttr;
use Carbon\Carbon;
use Livewire\Component;

class GreetingComponent extends Component
{
    public $name;

    public function mount($name)
    {
        $this->name = $name;
    }

    function checkOut()
    {
        $attendance = Attendance::where('current_in', true)
            ->whereDate('date', now())->get();

        foreach ($attendance as $att) {
            $attr = AttendanceAttr::where(['attendance_id' => $att->id])
                ->whereNull('time_out')->first();

            if ($attr) {
                $attr->update([
                    'time_out' => now(),
                ]);


                $att->update([
                    'current_in' => false,
                    'total_time' => $att->total_time + now()->diffInSeconds($attr->time_in),
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

        $query = Attendance::query();

        $query->whereBetween('date', [
            Carbon::now()->startOfDay(),
            Carbon::now()->endOfDay()
        ]);

        $attendances = $query->with(['student'])->get();
        $totalStudents = $attendances->count();
        $averageAttendanceDuration = $attendances->avg('total_time');

        $studentsInAttendanceToday = Attendance::whereDate('date', now())
            ->where('current_in', true)
            ->get()
            ->count();

        return view('livewire.dashboard.greeting-component', [
            'greeting' => $greeting,
            'studentsInAttendanceToday' => $studentsInAttendanceToday,
            'totalStudents' => $totalStudents,
            'averageAttendanceDuration' => $this->secondsToHms($averageAttendanceDuration),
        ]);
    }
}
