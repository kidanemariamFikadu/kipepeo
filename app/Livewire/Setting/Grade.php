<?php

namespace App\Livewire\Setting;

use Livewire\Attributes\Validate;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class Grade extends ModalComponent
{
    #[Validate('required|min:3|max:255|unique:grades,grade')]        
    public $grade;

    public $gradeId;

    public function mount($gradeId = null)
    {
        $this->gradeId = $gradeId;
        if ($gradeId) {
            $grade = \App\Models\Grade::find($gradeId);
            $this->grade = $grade->grade;
        }
    }

    function createGrade()
    {
        $this->validate();
        if ($this->gradeId) {
            $checkDuplicate = \App\Models\Grade::where('grade', $this->grade)->where('id', '!=', $this->gradeId)->first();
            if ($checkDuplicate) {
                $this->dispatch('MessageChanged', ['type' => 'error', 'content' => 'Grade already exists']);
                return;
            }

            $grade = \App\Models\Grade::find($this->gradeId);
            $grade->update(['grade' => $this->grade]);
            $this->dispatch('MessageChanged', ['type' => 'success', 'content' => 'Grade updated successfully']);
            $this->dispatch('grade-changed');
            $this->closeModal();
        } else {

            $checkDuplicate = \App\Models\Grade::where('grade', $this->grade)->first();
            if ($checkDuplicate) {
                $this->dispatch('MessageChanged', ['type' => 'error', 'content' => 'Grade already exists']);
                return;
            } else {
                \App\Models\Grade::create(['grade' => $this->grade]);
                $this->dispatch('MessageChanged', ['type' => 'success', 'content' => 'Grade created successfully']);
                $this->grade = '';
                $this->dispatch('grade-changed');
                $this->closeModal();
            }
        }
    }
    
    public function render()
    {
        return view('livewire.setting.grade');
    }
}
