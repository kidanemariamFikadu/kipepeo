<?php

namespace App\Livewire\Report;

use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Report')]
class Index extends Component
{
    public function render()
    {
        return view('livewire.report.index');
    }
}
