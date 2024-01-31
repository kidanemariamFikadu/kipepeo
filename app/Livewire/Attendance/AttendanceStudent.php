<?php

namespace App\Livewire\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceAttr;
use App\Models\School;
use App\Models\Student;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AttendanceStudent extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $currentlyIn = '';

    #[Url(history: true)]
    public $sortBy = 'created_at';

    #[Url(history: true)]
    public $sortDir = 'DESC';

    #[Url()]
    public $perPage = 5;

    public function getSchoolListProperty()
    {
        return School::all();
    }

    public function setSortBy($sortByField)
    {

        if ($this->sortBy === $sortByField) {
            $this->sortDir = ($this->sortDir == "ASC") ? 'DESC' : "ASC";
            return;
        }

        $this->sortBy = $sortByField;
        $this->sortDir = 'DESC';
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

        $attendance->update([
            'current_in' => false,
            'total_time' => $attendance->total_time + now()->diffInSeconds($attr->time_in),
        ]);

        session()->flash('success', 'Student checked out successfully');
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
        }else{
            $attendance->update([
                'current_in' => true,
            ]);
        }

        AttendanceAttr::create([
            'attendance_id' => $attendance->id,
            'student_id' => $student->id,
            'date' => now(),
            'time_in' => now(),
        ]);

        session()->flash('success', 'Student checked in successfully');
    }

    public function render()
    {
        return view('livewire.attendance.attendance-student', [
            'students' => Student::search($this->search)
                ->when($this->currentlyIn !== '', function ($query) {
                    $query->whereHas('attendances', function ($q) {
                        $q //->where('current_in', $this->currentlyIn)
                            ->whereDate('date', now())->first()?->current_in;
                    });
                })
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate($this->perPage)
        ])->title('Attendance');
    }
}
