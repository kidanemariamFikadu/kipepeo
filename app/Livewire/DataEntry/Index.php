<?php

namespace App\Livewire\DataEntry;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Data Entry')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.data-entry.index');
    }
}
