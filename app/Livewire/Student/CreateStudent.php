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

    #[Computed]
    public function getSchoolsProperty()
    {
        return School::all();
    }

    function create()
    {
        $this->form->validate();
        $student = StudentService::create($this->form);
        session()->flash('success', 'Student created successfully');
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
