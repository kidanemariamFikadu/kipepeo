<?php

namespace App\Livewire\Student;

use App\Models\School;
use App\Models\SchoolStudent;
use LivewireUI\Modal\ModalComponent;

class AddSchool extends ModalComponent
{

    public $student_id;
    public $school_id;

    public function mount($studentId = null)
    {
        if ($studentId) {
            $this->student_id = $studentId;
        }
    }

    public function getSchoolListProperty()
    {
        return School::all();
    }

    function createSchool()
    {
        $this->validate([
            'school_id' => 'required|exists:schools,id',
        ]);

        $checkDuplicate = SchoolStudent::where('student_id', $this->student_id)
            ->where('school_id', $this->school_id)
            ->first();

        if ($checkDuplicate) {
            session()->flash('error', 'School already exists.');
            return;
        }

        SchoolStudent::where('student_id', $this->student_id)
            ->update(['is_current' => false]);

        SchoolStudent::create([
            'school_id' => $this->school_id,
            'student_id' => $this->student_id,
            'is_current' => true,
        ]);
        $this->closeModal();

        session()->flash('success', 'School added successfully.');
        $this->dispatch('student-changed', []);
        $this->school_id = null;
    }

    public function render()
    {
        return view('livewire.student.add-school');
    }
}
