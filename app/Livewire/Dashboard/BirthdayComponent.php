<?php

namespace App\Livewire\Dashboard;

use App\Models\Student;
use Carbon\Carbon;
use Livewire\Component;

class BirthdayComponent extends Component
{
    public function render()
    {
        $currentWeekBirthdays =Student::whereRaw("DAYOFYEAR(dob) BETWEEN DAYOFYEAR(NOW()) AND DAYOFYEAR(NOW() + INTERVAL 1 WEEK)")
        ->get();

        return view('livewire.dashboard.birthday-component', [
            'currentWeekBirthdays' => $currentWeekBirthdays,
        ]);
    }
}
