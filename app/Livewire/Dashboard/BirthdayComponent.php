<?php

namespace App\Livewire\Dashboard;

use App\Models\Student;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class BirthdayComponent extends Component
{
    use WithPagination;

    public function render()
    {
        $currentWeekBirthdays = Student::whereRaw("DAYOFYEAR(dob) BETWEEN DAYOFYEAR(NOW()) AND DAYOFYEAR(NOW() + INTERVAL 1 WEEK)")
            ->orderByRaw('DAYOFYEAR(dob)')
            ->paginate(5, ['*'], 'birthdays-page');

        return view('livewire.dashboard.birthday-component', [
            'currentWeekBirthdays' => $currentWeekBirthdays,
        ]);
    }
}
