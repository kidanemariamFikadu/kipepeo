<?php

namespace App\Livewire\Volunteer;

use App\Enums\VolunteerStatus;
use App\Models\Volunteer;
use App\Models\VolunteerAttendance;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Component;

class VolunteerDetail extends Component
{
    public $volunteerId;
    public $volunteerDetails;

    public $name;
    public $phone;
    public $email;
    public $notes;
    public $status;
    public $hourlyRate;

    public $earningsFromDate;
    public $earningsToDate;
    public $earningsDaysPresent = 0;
    public $earningsTotalSeconds = 0;
    public $estimatedEarnings;

    #[On('MessageChanged')]
    function updateList($message)
    {
        if ($message)
            session()->flash($message['type'], $message['content']);
    }

    private function loadVolunteer($id)
    {
        return Volunteer::with([
            'attendances' => fn ($query) => $query->orderByDesc('date')->limit(15),
            'attendances.attrs',
            'activities' => fn ($query) => $query->orderByDesc('date')->limit(20),
            'activities.activityType',
            'activities.students',
        ])->find($id);
    }

    public function statuses()
    {
        return VolunteerStatus::cases();
    }

    function mount()
    {
        $volunteer = $this->loadVolunteer(request()->route('volunteer_id'));

        if (! $volunteer) {
            abort(404);
        }

        $this->volunteerId = $volunteer->id;
        $this->volunteerDetails = $volunteer;
        $this->name = $volunteer->name;
        $this->phone = $volunteer->phone;
        $this->email = $volunteer->email;
        $this->notes = $volunteer->notes;
        $this->status = $volunteer->status->value;
        $this->hourlyRate = $volunteer->hourly_rate;

        $this->earningsFromDate = now()->startOfMonth()->format('Y-m-d');
        $this->earningsToDate = now()->format('Y-m-d');
        $this->calculateEarnings();
    }

    public function calculateEarnings()
    {
        $this->validate([
            'earningsFromDate' => 'required|date',
            'earningsToDate' => 'required|date|after_or_equal:earningsFromDate',
        ]);

        $attendances = VolunteerAttendance::where('volunteer_id', $this->volunteerId)
            ->whereBetween('date', [$this->earningsFromDate, $this->earningsToDate])
            ->get();

        $this->earningsDaysPresent = $attendances->count();
        $this->earningsTotalSeconds = $attendances->sum('total_time');
        $this->estimatedEarnings = $this->hourlyRate
            ? round($this->earningsTotalSeconds / 3600 * $this->hourlyRate, 2)
            : null;
    }

    function update()
    {
        $this->validate([
            'name' => ['required', 'min:2', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::enum(VolunteerStatus::class)],
            'hourlyRate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $volunteer = Volunteer::findOrFail($this->volunteerId);
        $volunteer->update([
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'notes' => $this->notes,
            'status' => $this->status,
            'hourly_rate' => $this->hourlyRate,
        ]);

        session()->flash('success', 'Volunteer updated successfully.');
        $this->dispatch('volunteer-changed');
        $this->calculateEarnings();
    }

    public function render()
    {
        $volunteer = $this->loadVolunteer($this->volunteerId);

        if (! $volunteer) {
            abort(404);
        }

        $this->volunteerDetails = $volunteer;

        return view('livewire.volunteer.volunteer-detail')->title($volunteer->name . ' Detail');
    }
}
