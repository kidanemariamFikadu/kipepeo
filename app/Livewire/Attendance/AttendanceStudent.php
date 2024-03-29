<?php

namespace App\Livewire\Attendance;

use App\Models\Attendance;
use App\Models\AttendanceAttr;
use App\Models\School;
use App\Models\Student;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Attendance')]
class AttendanceStudent extends Component
{
    use WithPagination;

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
    public $currentlyIn = '';

    #[Url(history: true)]
    public $sortBy = 'name';

    #[Url(history: true)]
    public $sortDir = 'ASC';

    #[Url()]
    public $perPage = 10;

    #[On('student-changed')]
    public function refreshStudents($message)
    {
        if ($message)
            session()->flash($message['type'], $message['content']);
    }

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

        $attendance->current_in = false;
        $attendance->total_time = $attendance->total_time + now()->diffInSeconds($attr->time_in);
        $attendance->save();

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
        } else {
            if ($attendance->current_in == false) {
                $attendance->update([
                    'current_in' => true,
                ]);
            }else{
                session()->flash('error', 'Student already checked in');
                return;
            }
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
        // $newQuery=
        return view('livewire.attendance.attendance-student', [
            // 'students' => Student::search($this->search)
            //     ->when($this->currentlyIn !== '' && $this->currentlyIn, function ($query) {
            //         $query->whereHas('attendances', function ($query) {
            //             $query->whereDate('created_at', now()->toDateString())->where('current_in', $this->currentlyIn);
            //         });
            //     })
            //     ->when($this->currentlyIn !== '' && !$this->currentlyIn, function ($query) {
            //         $query->whereHas('attendances', function ($query) {
            //             $query->whereDate('created_at', now()->toDateString())->where('current_in', $this->currentlyIn);
            //         })->orWhereDoesntHave('attendances');
            //     })
            //     ->orderBy($this->sortBy, $this->sortDir)
            //     ->paginate($this->perPage)
            'students' => Student::search($this->search)
                ->when($this->currentlyIn !== '' && $this->currentlyIn, function ($query) {
                    $query->whereHas('attendances', function ($query) {
                        $query->whereDate('created_at', now()->toDateString())->where('current_in', $this->currentlyIn);
                    });
                })
                ->when($this->currentlyIn !== '' && !$this->currentlyIn, function ($query) {
                    $query->where(function ($query) {
                        $query->whereHas('attendances', function ($query) {
                            $query->whereDate('created_at', now()->toDateString())->where('current_in', $this->currentlyIn);
                        })->orWhereDoesntHave('attendances');
                    });
                })
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate($this->perPage)
        ]);
    }
}
