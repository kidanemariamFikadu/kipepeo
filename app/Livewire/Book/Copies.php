<?php

namespace App\Livewire\Book;

use App\Models\Book;
use App\Models\BookCopy;
use Livewire\Component;

class Copies extends Component
{
    var $bookId;
    // var $copies;

    public function mount($bookId)
    {
        $this->bookId = $bookId;
        // $this->copies = BookCopy::where('book_id', $bookId)->paginate();
    }
    public function render()
    {
        return view('livewire.book.copies',[
            'copies' => BookCopy::where('book_id', $this->bookId)->paginate() 
        ]);
    }
}
