<?php

namespace App\Livewire\Student;

use App\Livewire\Forms\CreateStudentForm;
use App\Models\School;
use App\Services\StudentService;
use Livewire\Attributes\Computed;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class CreateStudent extends ModalComponent
{
    public CreateStudentForm $form;
    public $show_details;
    public $isDataEntry;

    function mount($isDataEntry = false)
    {
        $this->isDataEntry = $isDataEntry;
    }

    #[Computed]
    public function getSchoolsProperty()
    {
        return School::all();
    }

    #[Computed]
    public function getGradesProperty()
    {
        return \App\Models\Grade::all();
    }

    function create()
    {
        if ($this->isDataEntry) {
            $this->form->validate([
                'name' => 'required|max:255',
                'gender' => 'required|in:male,female,other',
                'grade' => 'required|exists:grades,id',
                'school' => 'required|exists:schools,id',
                'guardian_name' => 'required|max:255',
                'guardian_phone' => 'required|phone:KE',
            ]);
        } else {
            $this->form->validate([
                'name' => 'required|max:255',
                'gender' => 'required|in:male,female,other',
                'dob' => 'required|date|before_or_equal:today',
                'grade' => 'required|exists:grades,id',
                'school' => 'required|exists:schools,id',
                'guardian_name' => 'required|max:255',
                'guardian_phone' => 'required|phone:KE',
            ]);
        }

        $student = StudentService::create($this->form);
        $this->dispatch('student-changed', ['type' => 'success', 'content' => 'Student created successfully', 'student' => $student]);
        $this->dispatch('dashboard-changed', ['type' => 'success', 'content' => 'Student created successfully']);
        if ($this->show_details) {
            return redirect()->route('student-detail', $student->id);
        } else {
            $this->closeModal();
        }
    }

    public function render()
    {
        return view('livewire.student.create-student');
    }
}
