<?php

namespace App\Livewire\Setting;

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class VolunteerList extends Component
{
    use WithPagination;

    public $search = '';

    #[On('volunteer-changed')]
    public function volunteerChanged()
    {
    }

    #[Computed]
    public function getVolunteerListProperty()
    {
        return \App\Models\Volunteer::search($this->search)->orderBy('name')->paginate(20);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.setting.volunteer-list');
    }
}
