<?php

namespace App\Livewire\DataEntry;

use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Data Entry')]
class Index extends Component
{
    #[On('student-changed')]
    public function studentChanged($message)
    {
        if ($message)
            session()->flash($message['type'], $message['content']);
    }
    public function render()
    {
        return view('livewire.data-entry.index');
    }
}
