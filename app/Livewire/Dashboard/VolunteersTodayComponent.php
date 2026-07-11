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
        return view('livewire.dashboard.volunteers-today-component', [
            'volunteersToday' => VolunteerAttendance::whereDate('date', today())
                ->distinct('volunteer_id')
                ->count('volunteer_id'),
            'volunteersCurrentlyIn' => VolunteerAttendance::whereDate('date', today())
                ->where('current_in', true)
                ->count(),
        ]);
    }
}
