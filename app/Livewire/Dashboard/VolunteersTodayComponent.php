<?php

namespace App\Livewire\Dashboard;

use App\Models\VolunteerAttendance;
use Livewire\Attributes\On;
use Livewire\Component;

class VolunteersTodayComponent extends Component
{
    #[On('dashboard-changed')]
    public function refreshDashboard()
    {
    }

    public function render()
    {
        $attendanceToday = VolunteerAttendance::whereDate('date', today())
            ->with(['attrs' => fn ($query) => $query->whereNull('time_out')])
            ->get();

        $totalSeconds = $attendanceToday->sum(function (VolunteerAttendance $attendance) {
            return $attendance->total_time + $attendance->attrs->sum(
                fn ($attr) => now()->diffInSeconds($attr->time_in, true)
            );
        });

        return view('livewire.dashboard.volunteers-today-component', [
            'volunteersToday' => $attendanceToday->count(),
            'volunteersCurrentlyIn' => $attendanceToday->where('current_in', true)->count(),
            'totalHoursToday' => round($totalSeconds / 3600, 1),
        ]);
    }
}
