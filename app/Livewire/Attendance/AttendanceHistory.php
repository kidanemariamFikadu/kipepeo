<?php

namespace App\Livewire\Attendance;

use App\Models\Student;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class AttendanceHistory extends ModalComponent
{
    public $date;
    public $studentId;
    public ?Student $student;

    public $attendance;

    // #[On("dateChanged")]
    function dateSelected($date)
    {dd('here');


        $student = Student::findOrFail($this->studentId);
        $attendance = $student->attendances()->whereDate('date', $date)->first();

        if ($attendance) {
            $this->attendance = $attendance;
        } else {
            $this->attendance = null;
        }
    }

    function mount(Student $student)
    {
        if ($student->exists) {
            $this->studentId = $student->id;
            $this->student = $student;
            $this->attendance = $student->attendances()->whereDate('date', now())->first();
        }

        $this->date = now()->format('Y-m-d');
    }

    public function render()
    {
        return view('livewire.attendance.attendance-history');
    }
}
