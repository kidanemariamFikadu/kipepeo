<?php

namespace App\Livewire\Attendance;

use App\Models\ActivityType;
use App\Models\Student;
use App\Models\Volunteer;
use App\Models\VolunteerActivity;
use App\Models\VolunteerAttendance;
use Livewire\Attributes\Computed;
use LivewireUI\Modal\ModalComponent;

class LogVolunteerActivity extends ModalComponent
{
    public $volunteerId;

    public ?Volunteer $volunteer;

    public $activityTypeId = '';

    public $studentIds = [];

    public $notes = '';

    function mount(Volunteer $volunteer)
    {
        if ($volunteer->exists) {
            $this->volunteerId = $volunteer->id;
            $this->volunteer = $volunteer;
        }
    }

    #[Computed]
    public function activityTypes()
    {
        return ActivityType::orderBy('name')->get();
    }

    #[Computed]
    public function eligibleStudents()
    {
        return Student::active()->orderBy('name')->get();
    }

    function logActivity()
    {
        $this->validate([
            'activityTypeId' => 'required|exists:activity_types,id',
            'studentIds' => 'nullable|array',
            'studentIds.*' => 'exists:students,id',
            'notes' => 'nullable|string|max:2000',
        ]);

        $attendance = VolunteerAttendance::where('volunteer_id', $this->volunteerId)
            ->whereDate('date', now())
            ->where('current_in', true)
            ->first();

        if (!$attendance) {
            $this->dispatch('MessageChanged', ['type' => 'error', 'content' => 'This volunteer is not currently checked in']);
            $this->dispatch('volunteer-changed', ['type' => 'error', 'content' => 'This volunteer is not currently checked in']);
            $this->closeModal();
            return;
        }

        $activity = VolunteerActivity::create([
            'volunteer_attendance_id' => $attendance->id,
            'volunteer_id' => $this->volunteerId,
            'activity_type_id' => $this->activityTypeId,
            'date' => now(),
            'notes' => $this->notes,
        ]);

        if (!empty($this->studentIds)) {
            $activity->students()->attach($this->studentIds);
        }

        $this->dispatch('MessageChanged', ['type' => 'success', 'content' => 'Activity logged successfully']);
        $this->dispatch('volunteer-changed', ['type' => 'success', 'content' => 'Activity logged successfully']);
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.attendance.log-volunteer-activity');
    }
}
