<?php

namespace App\Livewire\Book;

use App\Models\Rental;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class BookOnRent extends Component
{
    var $search;
    public $sortBy = 'due_at';

    #[Url(history: true)]
    public $sortDir = 'ASC';

    #[Url(history: true)]
    public $perPage = 10;

    public $status = 'all';

    #[On('rental-changed')]
    public function refreshBooks($message)
    {
        session()->flash($message['type'], $message['content']);
    }
    public function render()
    {
        $query = Rental::with(['book', 'checkedOutBy', 'checkedOutTo']);

        if ($this->status == 'returned') {
            $query->whereNotNull('returned_at');
        } elseif ($this->status == 'overdue') {
            $query->where('due_at', '<', now())->whereNull('returned_at');
        } elseif ($this->status == 'borrowed') {
            $query->where('returned_at', null);
        }

        $bookOnRent = $query->search($this->search)->orderBy($this->sortBy, $this->sortDir)->paginate($this->perPage);
        return view('livewire.book.book-on-rent', [
            'booksOnRent' => $bookOnRent
        ]);
    }
}
