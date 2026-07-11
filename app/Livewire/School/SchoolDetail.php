<?php

namespace App\Livewire\School;

use App\Models\School;
use App\Models\SchoolStudent;
use Livewire\Attributes\On;
use Livewire\Component;

class SchoolDetail extends Component
{
    public $schoolId;
    public $schoolDetails;

    #[On('school-changed')]
    function refreshSchool()
    {
        //
    }

    private function loadSchool($id)
    {
        return School::find($id);
    }

    private function loadRoster($id)
    {
        return SchoolStudent::where('school_id', $id)
            ->with([
                'student',
                'student.grades' => fn ($query) => $query->where('is_current', true)->with('gradeTable'),
            ])
            ->get()
            ->sortBy([
                ['is_current', 'desc'],
                ['student.name', 'asc'],
            ])
            ->values();
    }

    function mount()
    {
        $school = $this->loadSchool(request()->route('school_id'));

        if (! $school) {
            abort(404);
        }

        $this->schoolId = $school->id;
        $this->schoolDetails = $school;
    }

    public function render()
    {
        $school = $this->loadSchool($this->schoolId);

        if (! $school) {
            abort(404);
        }

        $this->schoolDetails = $school;

        return view('livewire.school.school-detail', [
            'roster' => $this->loadRoster($this->schoolId),
        ])->title($school->name . ' Detail');
    }
}
