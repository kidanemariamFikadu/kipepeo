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
        // active() keeps former students off a dashboard everyone sees --
        // an alumnus's name and birth date is not something the whole team
        // needs surfaced years after they left.
        $currentWeekBirthdays = Student::active()
            ->whereRaw("DAYOFYEAR(dob) BETWEEN DAYOFYEAR(NOW()) AND DAYOFYEAR(NOW() + INTERVAL 1 WEEK)")
            ->orderByRaw('DAYOFYEAR(dob)')
            ->paginate(5, ['*'], 'birthdays-page');

        return view('livewire.dashboard.birthday-component', [
            'currentWeekBirthdays' => $currentWeekBirthdays,
        ]);
    }
}
