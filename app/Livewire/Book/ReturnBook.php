<?php

namespace App\Livewire\Book;

use App\Models\Rental;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class ReturnBook extends ModalComponent
{
    var $comment;
    public $rentalId;

    public Rental $rental;

    function mount($rentalId)
    {
        $this->rentalId = $rentalId;
        $this->rental = Rental::with(['book', 'checkedOutBy', 'checkedOutTo'])->find($rentalId);
    }

    public function returnBook()
    {
        $this->rental->update([
            'returned_at' => now(),
            'comment' => $this->comment
        ]);

        $this->dispatch('rental-changed', ['type' => 'success', 'content' => 'Book returned successfully']);
        $this->closeModal();
    }

    public function render()
    {
        return view('livewire.book.return-book');
    }
}
