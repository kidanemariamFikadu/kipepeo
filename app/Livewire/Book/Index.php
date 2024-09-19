<?php

namespace App\Livewire\Book;

use App\Models\Book;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.book.index');
    }
}
