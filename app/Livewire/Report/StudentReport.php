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

    public function render()
    {
        return view('livewire.report.student-report', [
            "schoolReport" => School::withCount([
                'students as total_students',
                'students as current_students' => function ($query) {
                    $query->where('is_current', true);
                },
                'students as male_students_count' => function ($query) {
                    $query->where('gender', 'Male');
                },
                'students as female_students_count' => function ($query) {
                    $query->where('gender', 'Female');
                },
                'students as other_students_count' => function ($query) {
                    $query->where('gender', 'Other');
                },
            ])->search($this->search)->orderBy('name')->paginate($this->perPage)
        ]);
    }
}
