<?php

namespace App\Livewire\Setting;

use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[On('MessageChanged')]
    public function messageChanged($message)
    {
        session()->flash($message['type'], $message['content']);
    }

    #[Computed]
    public function getJobTitleListProperty()
    {
        return \App\Models\JobTitle::paginate(10);
    }

    function removeJobTitle($jobTitleId)
    {
        $checkEmployeeExist = \App\Models\User::where('job_title_id', $jobTitleId)->first();
        if ($checkEmployeeExist) {
            session()->flash('error', 'Job Title cannot be deleted as employees are associated with this job title');
            return;
        }

        \App\Models\JobTitle::find($jobTitleId)->delete();
        session()->flash('success', 'Job Title deleted successfully');
    }
    public function render()
    {
        return view('livewire.setting.index')->title('Setting');
    }
}
