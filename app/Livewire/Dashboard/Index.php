<?php

namespace App\Livewire\Dashboard;

use App\Models\Student;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{

    #[On('dashboard-changed')]
    function refreshDashboard($message)
    {
        if ($message)
            session()->flash($message['type'], $message['content']);
    }
    
    public function render()
    {
        return view('livewire.dashboard.index');
    }
}
