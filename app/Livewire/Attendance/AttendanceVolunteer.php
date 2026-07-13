<?php

namespace App\Livewire\Attendance;

use App\Models\Volunteer;
use App\Models\VolunteerAttendance;
use App\Models\VolunteerAttendanceAttr;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Volunteers')]
class AttendanceVolunteer extends Component
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

    #[On('volunteer-changed')]
    public function refreshVolunteers($message = null)
    {
        if ($message)
            session()->flash($message['type'], $message['content']);
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

    public function checkOut($volunteerId)
    {
        $volunteer = Volunteer::findOrFail($volunteerId);
        $attendance = VolunteerAttendance::where('volunteer_id', $volunteer->id)
            ->whereDate('date', now())->first();

        $attr = VolunteerAttendanceAttr::where(['volunteer_attendance_id' => $attendance->id, 'time_out' => null])->first();

        $attr->update([
            'time_out' => now(),
        ]);

        $attendance->current_in = false;
        $attendance->total_time = $attendance->total_time + now()->diffInSeconds($attr->time_in, true);
        $attendance->save();

        session()->flash('success', 'Volunteer checked out successfully');
    }

    public function checkIn($volunteerId)
    {
        $volunteer = Volunteer::findOrFail($volunteerId);
        $attendance = VolunteerAttendance::where('volunteer_id', $volunteer->id)
            ->whereDate('date', now())->first();
        if (!$attendance) {
            $attendance = VolunteerAttendance::create([
                'volunteer_id' => $volunteer->id,
                'date' => now(),
                'current_in' => true,
            ]);
        } else {
            if ($attendance->current_in == false) {
                $attendance->update([
                    'current_in' => true,
                ]);
            } else {
                session()->flash('error', 'Volunteer already checked in');
                return;
            }
        }

        VolunteerAttendanceAttr::create([
            'volunteer_attendance_id' => $attendance->id,
            'volunteer_id' => $volunteer->id,
            'date' => now(),
            'time_in' => now(),
        ]);

        session()->flash('success', 'Volunteer checked in successfully');
    }

    public function render()
    {
        return view('livewire.attendance.attendance-volunteer', [
            'volunteers' => Volunteer::search($this->search)
                ->active()
                ->with([
                    'attendances' => fn ($query) => $query->whereDate('date', now()),
                ])
                ->when($this->currentlyIn !== '' && $this->currentlyIn, function ($query) {
                    $query->whereHas('attendances', function ($query) {
                        $query->whereDate('date', now()->toDateString())->where('current_in', $this->currentlyIn);
                    });
                })
                ->when($this->currentlyIn !== '' && !$this->currentlyIn, function ($query) {
                    $query->where(function ($query) {
                        $query->whereHas('attendances', function ($query) {
                            $query->whereDate('date', now()->toDateString())->where('current_in', $this->currentlyIn);
                        })->orWhereDoesntHave('attendances');
                    });
                })
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate($this->perPage)
        ]);
    }
}
