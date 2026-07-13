<?php

namespace App\Livewire\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceAttr;
use App\Models\Student;
use Livewire\Attributes\Computed;
use LivewireUI\Modal\ModalComponent;

class QuickCheckInStudents extends ModalComponent
{
    public $search = '';

    public static function modalMaxWidth(): string
    {
        return '3xl';
    }

    #[Computed]
    public function results()
    {
        return Student::search($this->search)
            ->active()
            ->with([
                'attendances' => fn ($query) => $query->whereDate('date', now()),
                'schools' => fn ($query) => $query->where('is_current', true)->with('school'),
                'grades' => fn ($query) => $query->where('is_current', true)->with('gradeTable'),
            ])
            ->orderBy('name')
            ->limit(8)
            ->get();
    }

    public function checkOut($studentId)
    {
        $student = Student::findOrFail($studentId);
        $attendance = Attendance::where('student_id', $student->id)
            ->whereDate('date', now())->first();

        $attr = AttendanceAttr::where(['attendance_id' => $attendance->id, 'time_out' => null])->first();

        $attr->update([
            'time_out' => now(),
        ]);

        $attendance->current_in = false;
        $attendance->total_time = $attendance->total_time + now()->diffInSeconds($attr->time_in, true);
        $attendance->save();

        $this->dispatch('dashboard-changed', ['type' => 'success', 'content' => 'Student checked out successfully']);
    }

    public function checkIn($studentId)
    {
        $student = Student::findOrFail($studentId);
        $attendance = Attendance::where('student_id', $student->id)
            ->whereDate('date', now())->first();
        if (!$attendance) {
            $attendance = Attendance::create([
                'student_id' => $student->id,
                'date' => now(),
                'current_in' => true,
            ]);
        } else {
            if ($attendance->current_in == false) {
                $attendance->update([
                    'current_in' => true,
                ]);
            } else {
                return;
            }
        }

        AttendanceAttr::create([
            'attendance_id' => $attendance->id,
            'student_id' => $student->id,
            'date' => now(),
            'time_in' => now(),
        ]);

        $this->dispatch('dashboard-changed', ['type' => 'success', 'content' => 'Student checked in successfully']);
    }

    public function render()
    {
        return view('livewire.attendance.quick-check-in-students');
    }
}
