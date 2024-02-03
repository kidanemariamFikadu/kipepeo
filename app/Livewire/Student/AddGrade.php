<?php

namespace App\Livewire\Student;

use App\Models\GradeStudent;
use Livewire\Attributes\Computed;
use LivewireUI\Modal\ModalComponent;

class AddGrade extends ModalComponent
{
    public $student_id;
    public $grade;

    public function mount($studentId = null)
    {
        if ($studentId) {
            $this->student_id = $studentId;
        }
    }

    #[Computed]
    function getGradesProperty()
    {
        return \App\Models\Grade::all();
    }

    function createGrade()
    {
        $this->validate([
            'grade' => 'required|exists:grades,id',
        ]);

        $checkDuplicate=GradeStudent::where('student_id', $this->student_id)
            ->where('grade', $this->grade)
            ->first();

        if($checkDuplicate){
            session()->flash('error', 'Grade already exists.');
            return;
        }

        GradeStudent::where('student_id', $this->student_id)
            ->update(['is_current' => false]);

        GradeStudent::create([
            'grade' => $this->grade,
            'student_id' => $this->student_id,
            'is_current' => true,
        ]);
        $this->closeModal();

        $this->dispatch('student-changed', ['type' => 'success', 'content' => 'Grade added successfully.']);
        $this->grade = null;
    }

    public function render()
    {
        return view('livewire.student.add-grade');
    }
}
