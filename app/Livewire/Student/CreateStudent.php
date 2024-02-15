<?php

namespace App\Livewire\Student;

use App\Livewire\Forms\CreateStudentForm;
use App\Models\School;
use App\Services\StudentService;
use Carbon\Carbon;
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
                'dob' => 'required|date',
                'grade' => 'required|exists:grades,id',
                'school' => 'required|exists:schools,id',
                'guardian_name' => 'required|max:255',
                'guardian_phone' => 'required|phone:KE',
            ]);

            $date18YearsAgo = Carbon::now()->subYears(5);

            $this->form->validate([
                'dob' => ['required', 'date', 'before:' . $date18YearsAgo],
            ]);
        }

        $student = StudentService::create($this->form);
        $this->dispatch('student-changed', ['type' => 'success', 'content' => 'Student created successfully', 'student' => $student]);
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
