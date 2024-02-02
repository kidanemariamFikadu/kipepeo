<?php

namespace App\Livewire\Setting;

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[On('school-changed')]
    public function schoolChanged()
    {
    }
    #[On('grade-changed')]
    public function gradeChanged()
    {
    }

    #[Computed]
    public function  getSchoolListProperty()
    {
        return \App\Models\School::paginate();
    }

    #[Computed]
    public function getGradeListProperty()
    {
        return \App\Models\Grade::paginate();
    }

    #[Computed]
    public function getJobTitleListProperty()
    {
        return \App\Models\JobTitle::paginate();
    }


    public function removeSchool($schooId)
    {
        $checkStudentExist = \App\Models\SchoolStudent::where('school_id', $schooId)->first();
        if ($checkStudentExist) {
            session()->flash('error', 'School cannot be deleted as students are associated with this school');
            return;
        }
        \App\Models\School::find($schooId)->delete();
        session()->flash('message', 'School deleted successfully');
    }

    function removeGrade($gradeId)
    {
        $checkStudentExist = \App\Models\GradeStudent::where('grade', $gradeId)->first();
        if ($checkStudentExist) {
            session()->flash('error', 'Grade cannot be deleted as students are associated with this grade');
            return;
        }
        \App\Models\Grade::find($gradeId)->delete();
        session()->flash('message', 'Grade deleted successfully');
    }

    function removeJobTitle($jobTitleId)
    {
        $checkEmployeeExist = \App\Models\User::where('job_title_id', $jobTitleId)->first();
        if ($checkEmployeeExist) {
            session()->flash('error', 'Job Title cannot be deleted as employees are associated with this job title');
            return;
        }
        
        \App\Models\JobTitle::find($jobTitleId)->delete();
        session()->flash('message', 'Job Title deleted successfully');
    }
    public function render()
    {
        return view('livewire.setting.index')->title('Setting');
    }
}
