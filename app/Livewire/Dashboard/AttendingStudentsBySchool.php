<?php

namespace App\Livewire\Dashboard;

use App\Models\Student;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class AttendingStudentsBySchool extends Component
{
    // public $studentsBySchool;
    use WithPagination;

    function getStudentsBySchoolProperty()
    {
        $today = Carbon::now()->toDateString();

        return \App\Models\School::withCount(['students' => function ($query) use ($today) {
            $query->whereHas('attendances', function ($query) use ($today) {
                $query->whereDate('date', $today);
            });
        }])
            ->having('students_count', '>', 0)
            ->paginate();

        // $schools = \App\Models\School::withCount(['students', 'attendances' => function ($query) use ($today) {
        //     $query->whereDate('dates', $today);
        // }])->get();

        // return $schools;
    }

    function getTotalStudentsAttendedTodayProperty()
    {
        return Student::whereHas('attendances', function ($query) {
            $query->whereDate('date', Carbon::now()->toDateString());
        })->count();
    }

    public function render()
    {
        return view('livewire.dashboard.attending-students-by-school');
    }
}
