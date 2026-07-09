<?php

namespace App\Livewire\Report;

use App\Models\School;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class StudentReport extends Component
{
    use WithPagination;


    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $school = '';

    #[Url(history: true)]
    public $sortBy = 'name';

    #[Url(history: true)]
    public $sortDir = 'ASC';

    #[Url()]
    public $perPage = 10;

    protected function baseQuery()
    {
        return School::withCount([
            'students as total_students',
            'students as current_students' => function ($query) {
                $query->where('is_current', true);
            },
            'students as male_students_count' => function ($query) {
                $query->whereRaw('LOWER(gender) = ?', ['male']);
            },
            'students as female_students_count' => function ($query) {
                $query->whereRaw('LOWER(gender) = ?', ['female']);
            },
            'students as other_students_count' => function ($query) {
                $query->whereRaw('LOWER(gender) = ?', ['other']);
            },
        ])->search($this->search)->orderBy('name');
    }

    public function render()
    {
        return view('livewire.report.student-report', [
            'schoolReport' => $this->baseQuery()->paginate($this->perPage),
            // The printed report must include every matching school, not just the
            // page currently visible on screen, so it's queried separately here
            // without pagination.
            'fullSchoolReport' => $this->baseQuery()->get(),
        ]);
    }
}
