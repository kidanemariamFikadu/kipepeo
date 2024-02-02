<?php

namespace App\Livewire\Setting;

use Livewire\Attributes\Validate;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class School extends ModalComponent
{
    #[Validate('required|min:3|max:255|unique:schools,name')]
    public $school;
    public $schoolId;

    public function mount($schoolId = null)
    {
        $this->schoolId = $schoolId;
        if ($schoolId) {
            $school = \App\Models\School::find($schoolId);
            $this->school = $school->name;
        }
    }

    function createSchool()
    {
        $this->validate();
        if ($this->schoolId) {
            $checkDuplicate = \App\Models\School::where('name', $this->school)->where('id', '!=', $this->schoolId)->first();
            if ($checkDuplicate) {
                session()->flash('error', 'School already exists');
                return;
            }

            $school = \App\Models\School::find($this->schoolId);
            $school->update(['name' => $this->school]);
            session()->flash('message', 'School updated successfully');
            $this->dispatch('school-changed');
            $this->closeModal();
        } else {

            $checkDuplicate = \App\Models\School::where('name', $this->school)->first();
            if ($checkDuplicate) {
                session()->flash('error', 'School already exists');
                return;
            } else {
                \App\Models\School::create(['name' => $this->school]);
                session()->flash('message', 'School created successfully');
                $this->school = '';
                $this->dispatch('school-changed');
                $this->closeModal();
            }
        }
    }

    public function render()
    {
        return view('livewire.setting.school');
    }
}
