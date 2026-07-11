<?php

namespace App\Livewire\Setting;

use Illuminate\Validation\Rule;
use LivewireUI\Modal\ModalComponent;

class ActivityType extends ModalComponent
{
    public $name;

    public $activityTypeId;

    public function mount($activityTypeId = null)
    {
        $this->activityTypeId = $activityTypeId;
        if ($activityTypeId) {
            $activityType = \App\Models\ActivityType::find($activityTypeId);
            $this->name = $activityType->name;
        }
    }

    function saveActivityType()
    {
        $this->validate([
            'name' => ['required', 'min:2', 'max:255', Rule::unique('activity_types', 'name')->ignore($this->activityTypeId)],
        ]);

        if ($this->activityTypeId) {
            \App\Models\ActivityType::find($this->activityTypeId)->update([
                'name' => $this->name,
            ]);
            $this->dispatch('MessageChanged', ['type' => 'success', 'content' => 'Activity type updated successfully']);
        } else {
            \App\Models\ActivityType::create([
                'name' => $this->name,
            ]);
            $this->dispatch('MessageChanged', ['type' => 'success', 'content' => 'Activity type created successfully']);
        }

        $this->dispatch('activity-type-changed');
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.setting.activity-type');
    }
}
