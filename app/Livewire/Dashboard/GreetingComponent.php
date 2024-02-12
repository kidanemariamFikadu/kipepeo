<?php

namespace App\Livewire\Dashboard;

use App\Models\Attendance;
use App\Models\AttendanceAttr;
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

        $studentsInAttendanceToday = Attendance::whereDate('date', now())
            ->where('current_in', true)
            ->get()
            ->count();

        return view('livewire.dashboard.greeting-component', [
            'greeting' => $greeting,
            'studentsInAttendanceToday' => $studentsInAttendanceToday
        ]);
    }
}
