<?php

namespace App\Livewire\DataEntry;

use App\Livewire\Forms\DataEntry\AttendanceForm;
use App\Models\Attendance;
use App\Models\AttendanceAttr;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class AddStudentAttendance extends Component
{
    public AttendanceForm $form;
    public $studentSelected;
    #[On('student-changed')]
    public function studentChanged($event)
    {
        $this->getStudentsProperty();
        $this->studentSelected = $event['student']['id'];
    }
    public function getStudentsProperty()
    {
        return \App\Models\Student::orderBy('name')->get();
    }

    function addAttendance()
    {
        $this->form->validate();


        $attendance = Attendance::where('student_id', $this->form->student_id)
            ->whereDate('date', $this->form->date)->first();

        if (!$attendance) {
            $attendance = Attendance::create([
                'student_id' => $this->form->student_id,
                'date' => $this->form->date,
                'current_in' => false,
                'total_time' => 0,
            ]);
        }

        $attr = AttendanceAttr::create([
            'student_id' => $this->form->student_id,
            'date' => $this->form->date,
            'attendance_id' => $attendance->id,
            'time_in' => $this->form->startTime,
            'time_out' => $this->form->endTime,
        ]);

        $currentDate = Carbon::now()->format('Y-m-d');

        $startTime = $this->form->startTime;
        $endTime = $this->form->endTime;
        $startDateTime = Carbon::parse("$currentDate $startTime");
        $endDateTime = Carbon::parse("$currentDate $endTime");
        
        $timeDifferenceInSeconds = $endDateTime->diffInSeconds($startDateTime);

        $attendance->current_in = false;
        $attendance->total_time = $attendance->total_time + $timeDifferenceInSeconds;
        $attendance->save();

        $this->form->date = '';
        $this->form->startTime = '';
        $this->form->endTime = '';
        $student= \App\Models\Student::find($this->form->student_id);
        $this->dispatch('student-changed', ['type' => 'success', 'content' => 'Student created successfully', 'student' => $student]);
    }


    public function render()
    {
        return view('livewire.data-entry.add-student-attendance');
    }
}
