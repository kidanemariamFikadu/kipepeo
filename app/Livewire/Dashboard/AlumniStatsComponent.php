<?php

namespace App\Livewire\Dashboard;

use App\Models\Student;
use Livewire\Component;

class AlumniStatsComponent extends Component
{
    public function render()
    {
        return view('livewire.dashboard.alumni-stats-component', [
            'totalAlumni' => Student::whereNotNull('graduated_at')->count(),
            'graduatedThisYear' => Student::whereNotNull('graduated_at')
                ->whereYear('graduated_at', now()->year)
                ->count(),
        ]);
    }
}
