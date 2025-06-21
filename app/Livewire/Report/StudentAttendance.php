<?php

namespace App\Livewire\Report;

use App\Models\Attendance;
use Livewire\Component;

class StudentAttendance extends Component
{
    public $date;
    public $students = [];

    function secondsToHms($seconds)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
    }


    public function getStudentByDate()
    {
        $this->validate([
            'date' => 'required|date',
        ]);

        $this->students = Attendance::whereDate('date', $this->date)
            ->with(['student', 'student.schools', 'student.guardians', 'attrs'])
            ->get()
            ->map(function ($attendance) {
                return [
                    'id' => $attendance->student->id,
                    'name' => $attendance->student->name,
                    'attributes' => $attendance->attrs->map(function ($attr) {
                        return [
                            'time_in' => $attr->time_in,
                            'time_out' => $attr->time_out,
                        ];
                    }),
                    'current_in' => $attendance->current_in,
                    'total_time' => $this->secondsToHms($attendance->total_time),
                    'school' => $attendance->student->schools->first()->name ?? 'N/A',
                    'guardians' => $attendance->student->guardians->map(function ($guardian) {
                        return [
                            'guardian_name' => $guardian->guardian_name,
                            'guardian_phone' => $guardian->guardian_phone,
                        ];
                    }),
                ];
            });
    }
    public function render()
    {
        return view('livewire.report.student-attendance', [
            'students' => $this->students,
        ]);
    }
}
