<?php

namespace App\Livewire\Setting;

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class GradeList extends Component
{
    use WithPagination;

    #[On('grade-changed')]
    public function gradeChanged()
    {
    }

    #[Computed]
    public function getGradeListProperty()
    {
        return \App\Models\Grade::all();
    }    

    function removeGrade($gradeId)
    {
        $checkStudentExist = \App\Models\GradeStudent::where('grade', $gradeId)->first();
        if ($checkStudentExist) {
            $this->dispatch('MessageChanged', ['type' => 'error', 'content' => 'Grade cannot be deleted as students are associated with this grade']);
            return;
        }
        \App\Models\Grade::find($gradeId)->delete();
        $this->dispatch('MessageChanged', ['type' => 'success', 'content' => 'Grade deleted successfully']);
    }

    public function render()
    {
        return view('livewire.setting.grade-list');
    }
}
