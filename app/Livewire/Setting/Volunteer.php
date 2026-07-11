<?php

namespace App\Livewire\Setting;

use App\Enums\VolunteerStatus;
use Illuminate\Validation\Rule;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class Volunteer extends ModalComponent
{
    public $name;

    public $phone;

    public $email;

    public $notes;

    public $status = 'active';

    public $hourlyRate;

    public $volunteerId;

    public function mount($volunteerId = null)
    {
        $this->volunteerId = $volunteerId;
        if ($volunteerId) {
            $volunteer = \App\Models\Volunteer::find($volunteerId);
            $this->name = $volunteer->name;
            $this->phone = $volunteer->phone;
            $this->email = $volunteer->email;
            $this->notes = $volunteer->notes;
            $this->status = $volunteer->status->value;
            $this->hourlyRate = $volunteer->hourly_rate;
        }
    }

    public function statuses()
    {
        return VolunteerStatus::cases();
    }

    function saveVolunteer()
    {
        $this->validate([
            'name' => ['required', 'min:2', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', Rule::enum(VolunteerStatus::class)],
            'hourlyRate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $data = [
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'notes' => $this->notes,
            'status' => $this->status,
            'hourly_rate' => $this->hourlyRate,
        ];

        if ($this->volunteerId) {
            \App\Models\Volunteer::find($this->volunteerId)->update($data);
            $message = 'Volunteer updated successfully';
        } else {
            \App\Models\Volunteer::create($data);
            $message = 'Volunteer created successfully';
        }

        $this->dispatch('MessageChanged', ['type' => 'success', 'content' => $message]);
        $this->dispatch('volunteer-changed', ['type' => 'success', 'content' => $message]);
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.setting.volunteer');
    }
}
