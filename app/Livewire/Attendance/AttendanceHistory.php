<?php

namespace App\Livewire\Attendance;

use App\Models\Student;
use Carbon\Carbon;
use LivewireUI\Modal\ModalComponent;

class AttendanceHistory extends ModalComponent
{
    public $date;
    public $studentId;
    public ?Student $student;

    public $attendance;

    public function updatedDate()
    {
        $this->search();
    }

    public function previousDay()
    {
        $this->date = Carbon::parse($this->date)->subDay()->format('Y-m-d');
        $this->search();
    }

    public function nextDay()
    {
        if ($this->isToday()) {
            return;
        }

        $this->date = Carbon::parse($this->date)->addDay()->format('Y-m-d');
        $this->search();
    }

    public function goToToday()
    {
        $this->date = now()->format('Y-m-d');
        $this->search();
    }

    public function isToday(): bool
    {
        return Carbon::parse($this->date)->isToday();
    }

    public function search()
    {
        $student = Student::findOrFail($this->studentId);

        $this->attendance = $student->attendances()->whereDate('date', $this->date)->first();
    }

    public function mount(Student $student)
    {
        if ($student->exists) {
            $this->studentId = $student->id;
            $this->student = $student;
        }

        $this->date = now()->format('Y-m-d');
        $this->search();
    }

    public function render()
    {
        return view('livewire.attendance.attendance-history');
    }
}
