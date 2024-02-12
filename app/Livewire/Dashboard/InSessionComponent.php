<?php

namespace App\Livewire\Dashboard;

use App\Models\Attendance;
use App\Models\AttendanceAttr;
use App\Models\Student;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class InSessionComponent extends Component
{
    use WithPagination;
    
    #[On('dashboard-changed')]
    function refreshDashboard($message)
    {
    }

    function checkOut($studentId)
    {
        $student = Student::findOrFail($studentId);
        $attendance = Attendance::where('student_id', $student->id)
            ->whereDate('date', now())->first();

        $attr = AttendanceAttr::where(['attendance_id' => $attendance->id, 'time_out' => null])->first();

        $attr->update([
            'time_out' => now(),
        ]);

        $attendance->update([
            'current_in' => false,
            'total_time' => $attendance->total_time + now()->diffInSeconds($attr->time_in),
        ]);

        $this->dispatch('dashboard-changed', ['type' => 'success', 'content' => 'Student checked out successfully']);
    }

    public function render()
    {
        $today = Carbon::now()->toDateString();

        $studentsInAttendanceToday = Attendance::whereDate('date', $today)
            ->where('current_in', true)
            ->with('student') // eager load the student relationship
            ->paginate();
            // ->pluck('student');

        return view('livewire.dashboard.in-session-component', [
            'inSessionStudents' => $studentsInAttendanceToday,
        ]);
    }
}
