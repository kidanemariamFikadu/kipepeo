<?php

namespace App\Livewire\Setting;

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class SchoolList extends Component
{
    use WithPagination;

    public $search = '';

    #[On('school-changed')]
    public function schoolChanged()
    {
    }

    #[Computed]
    public function  getSchoolListProperty()
    {
        return \App\Models\School::where("name", "like", "%" . $this->search . "%")->orderBy("name")->paginate(20);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }


    public function removeSchool($schooId)
    {
        $checkStudentExist = \App\Models\SchoolStudent::where('school_id', $schooId)->first();
        if ($checkStudentExist) {

            $this->dispatch('MessageChanged', ['type' => 'error', 'content' => 'School cannot be deleted as students are associated with this school']);
            return;
        }
        \App\Models\School::find($schooId)->delete();
        $this->dispatch('MessageChanged', ['type' => 'success', 'content' => 'School deleted successfully']);
    }

    public function render()
    {
        return view('livewire.setting.school-list');
    }
}
