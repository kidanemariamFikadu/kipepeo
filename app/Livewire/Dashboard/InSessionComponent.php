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

    public $search = '';

    #[On('dashboard-changed')]
    function refreshDashboard($message)
    {
    }

    public function updatedSearch()
    {
        $this->resetPage('in-session-page');
    }

    function checkOut($studentId)
    {
        $attendance = Attendance::findOrFail($studentId);

        $attr = AttendanceAttr::where(['attendance_id' => $attendance->id, 'time_out' => null])->first();

        $attr->update([
            'time_out' => now(),
        ]);

        $attendance->update([
            'current_in' => false,
            'total_time' => $attendance->total_time + now()->diffInSeconds($attr->time_in, true),
        ]);

        $this->dispatch('dashboard-changed', ['type' => 'success', 'content' => 'Student checked out successfully']);
    }

    public function render()
    {
        $today = Carbon::now()->toDateString();

        $studentsInAttendanceToday = Attendance::whereDate('date', $today)
            ->where('current_in', true)
            ->whereHas('student', fn ($query) => $query->when($this->search, fn ($query) => $query->search($this->search)))
            ->with(['student.rentals' => fn ($query) => $query->overdue()->with('book')])
            ->paginate(6, ['*'], 'in-session-page');

        return view('livewire.dashboard.in-session-component', [
            'inSessionStudents' => $studentsInAttendanceToday,
        ]);
    }
}
