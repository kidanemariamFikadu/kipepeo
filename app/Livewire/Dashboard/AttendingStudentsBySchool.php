<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Concerns\HasSortableColumns;
use App\Models\School;
use App\Models\Student;
use Carbon\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AttendingStudentsBySchool extends Component
{
    use HasSortableColumns;
    use WithPagination;

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $sortBy = 'students_count';

    #[Url(history: true)]
    public $sortDir = 'DESC';

    #[Url()]
    public $perPage = 10;

    public function updatedSearch()
    {
        $this->resetPage('schools-page');
    }

    function getStudentsBySchoolProperty()
    {
        $today = Carbon::now()->toDateString();

        return School::withCount(['students' => function ($query) use ($today) {
            $query->whereHas('attendances', function ($query) use ($today) {
                $query->whereDate('date', $today);
            });
        }])
            ->having('students_count', '>', 0)
            ->search($this->search)
            ->orderBy($this->sortBy, $this->sortDir)
            ->paginate($this->perPage, ['*'], 'schools-page');
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
