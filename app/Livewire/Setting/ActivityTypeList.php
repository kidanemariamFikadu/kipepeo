<?php

namespace App\Livewire\Setting;

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class ActivityTypeList extends Component
{
    #[On('activity-type-changed')]
    public function activityTypeChanged()
    {
    }

    #[On('MessageChanged')]
    public function messageChanged($message)
    {
        session()->flash($message['type'], $message['content']);
    }

    #[Computed]
    public function getActivityTypeListProperty()
    {
        return \App\Models\ActivityType::orderBy('name')->get();
    }

    function removeActivityType($activityTypeId)
    {
        $activityType = \App\Models\ActivityType::find($activityTypeId);

        if ($activityType->activities()->exists()) {
            $this->dispatch('MessageChanged', ['type' => 'error', 'content' => 'Activity type cannot be deleted as activities are associated with it']);
            return;
        }

        $activityType->delete();
        $this->dispatch('MessageChanged', ['type' => 'success', 'content' => 'Activity type deleted successfully']);
    }

    public function render()
    {
        return view('livewire.setting.activity-type-list');
    }
}
