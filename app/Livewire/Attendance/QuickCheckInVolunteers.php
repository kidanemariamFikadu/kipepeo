<?php

namespace App\Livewire\Attendance;

use App\Models\Volunteer;
use App\Models\VolunteerAttendance;
use App\Models\VolunteerAttendanceAttr;
use Livewire\Attributes\Computed;
use LivewireUI\Modal\ModalComponent;

class QuickCheckInVolunteers extends ModalComponent
{
    public $search = '';

    public static function modalMaxWidth(): string
    {
        return '3xl';
    }

    #[Computed]
    public function results()
    {
        return Volunteer::search($this->search)
            ->active()
            ->with([
                'attendances' => fn ($query) => $query->whereDate('date', now()),
            ])
            ->orderBy('name')
            ->limit(8)
            ->get();
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

        $this->dispatch('dashboard-changed', ['type' => 'success', 'content' => 'Volunteer checked out successfully']);
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
                return;
            }
        }

        VolunteerAttendanceAttr::create([
            'volunteer_attendance_id' => $attendance->id,
            'volunteer_id' => $volunteer->id,
            'date' => now(),
            'time_in' => now(),
        ]);

        $this->dispatch('dashboard-changed', ['type' => 'success', 'content' => 'Volunteer checked in successfully']);
    }

    public function render()
    {
        return view('livewire.attendance.quick-check-in-volunteers');
    }
}
