<?php

namespace App\Livewire\Manual;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('User Manual')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.manual.index');
    }
}
