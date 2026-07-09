<?php

namespace App\Livewire\Setting;

use App\Enums\ActivityCategory;
use Illuminate\Validation\Rule;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class ActivityType extends ModalComponent
{
    public $name;

    public $category;

    public $activityTypeId;

    public function mount($activityTypeId = null)
    {
        $this->activityTypeId = $activityTypeId;
        if ($activityTypeId) {
            $activityType = \App\Models\ActivityType::find($activityTypeId);
            $this->name = $activityType->name;
            $this->category = $activityType->category?->value;
        }
    }

    public function categories()
    {
        return ActivityCategory::cases();
    }

    function saveActivityType()
    {
        $this->validate([
            'name' => ['required', 'min:2', 'max:255', Rule::unique('activity_types', 'name')->ignore($this->activityTypeId)],
            'category' => ['nullable', Rule::enum(ActivityCategory::class)],
        ]);

        if ($this->activityTypeId) {
            \App\Models\ActivityType::find($this->activityTypeId)->update([
                'name' => $this->name,
                'category' => $this->category,
            ]);
            $this->dispatch('MessageChanged', ['type' => 'success', 'content' => 'Activity type updated successfully']);
        } else {
            \App\Models\ActivityType::create([
                'name' => $this->name,
                'category' => $this->category,
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
