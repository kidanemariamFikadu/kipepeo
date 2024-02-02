<?php

namespace App\Livewire\Student;

use App\Livewire\Forms\student\AddStudentGuardianForm;
use App\Models\StudentGuardian;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class AddGuardian extends ModalComponent
{
    public AddStudentGuardianForm $addStudentGuardianForm;
    public ?StudentGuardian $studentGuardian;
    public $guardian_id;

    public $student_id;

    public function mount(StudentGuardian $studentGuardian, $studentId = null)
    {
        if ($studentGuardian->exists) {
            $this->addStudentGuardianForm->guardian_name = $studentGuardian->guardian_name;
            $this->addStudentGuardianForm->guardian_phone = $studentGuardian->guardian_phone;
            $this->addStudentGuardianForm->is_primary = ($studentGuardian->is_primary) ? true : false;
            $this->addStudentGuardianForm->student_id = $studentGuardian->student_id;
            $this->guardian_id = $studentGuardian->id;
        }

        if ($studentId) {
            $this->student_id = $studentId;
        }
    }

    function createGuardian()
    {
        // dd($this->all());
        if (!$this->guardian_id) {
            $this->addStudentGuardianForm->student_id = $this->student_id;
        }
        $this->addStudentGuardianForm->validate();

        if (!$this->guardian_id) {
            StudentGuardian::create([
                'guardian_name' => $this->addStudentGuardianForm->guardian_name,
                'guardian_phone' => $this->addStudentGuardianForm->guardian_phone,
                'is_primary' => $this->addStudentGuardianForm->is_primary ? true : false,
                'student_id' => $this->addStudentGuardianForm->student_id,
            ]);
            session()->flash('success', 'Guardian added successfully.');
            $this->dispatch('student-changed', []);
            $this->closeModal();
            $this->addStudentGuardianForm->reset();
        } else {
            $guardian = StudentGuardian::find($this->guardian_id);
            $guardian->update([
                'guardian_name' => $this->addStudentGuardianForm->guardian_name,
                'guardian_phone' => $this->addStudentGuardianForm->guardian_phone,
                'is_primary' => $this->addStudentGuardianForm->is_primary,
            ]);
            session()->flash('success', 'Guardian updated successfully.');
            $this->dispatch('student-changed', []);
            $this->closeModal();
            $this->guardian_id = null;
            $this->addStudentGuardianForm->reset();
        }
    }
    public function render()
    {
        return view('livewire.student.add-guardian');
    }
}
