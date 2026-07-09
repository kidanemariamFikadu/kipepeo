<?php

namespace App\Livewire\Book;

use App\Models\BookCopy;
use Livewire\Component;

class Copies extends Component
{
    public $bookId;

    public function mount($bookId)
    {
        $this->bookId = $bookId;
    }

    public function markAsLost($copyId)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        BookCopy::where('id', $copyId)->where('book_id', $this->bookId)->firstOrFail()->markAsLost();
        session()->flash('success', 'Copy marked as lost.');
    }

    public function markAsStolen($copyId)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        BookCopy::where('id', $copyId)->where('book_id', $this->bookId)->firstOrFail()->markAsStolen();
        session()->flash('success', 'Copy marked as stolen.');
    }

    public function markAsAvailable($copyId)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        BookCopy::where('id', $copyId)->where('book_id', $this->bookId)->firstOrFail()->update(['status' => 'available']);
        session()->flash('success', 'Copy marked as available.');
    }

    public function render()
    {
        return view('livewire.book.copies', [
            'copies' => BookCopy::where('book_id', $this->bookId)->paginate()
        ]);
    }
}
