<?php

namespace App\Livewire\Setting;

use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class JobTitle extends ModalComponent
{

    public $jobTitle;
    public $jobTitleId;

    protected $rules = [
        'jobTitle' => 'required|unique:job_titles,name,'
    ];

    public function mount($jobTitleId = null)
    {
        $this->jobTitleId = $jobTitleId;
        if ($jobTitleId) {
            $jobTitleRaw = \App\Models\JobTitle::find($jobTitleId);
            $this->jobTitle = $jobTitleRaw->name;
        }
    }

    public function save()
    {
        $this->validate();
        if ($this->jobTitleId) {
            $jobTitle = \App\Models\JobTitle::find($this->jobTitleId);
            $jobTitle->name = $this->jobTitle;
            $jobTitle->save();
            session()->flash('message', 'Job Title updated successfully');
        } else {
            \App\Models\JobTitle::create(['name' => $this->jobTitle]);
            session()->flash('message', 'Job Title added successfully');
        }
        
        $this->dispatch('school-changed');
        $this->jobTitle = '';
        $this->closeModal();
    }


    public function render()
    {
        return view('livewire.setting.job-title');
    }
}
