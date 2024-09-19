<?php

namespace App\Livewire\Book;

use App\Models\Book;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class BookList extends Component
{
    var $search;
    public $sortBy = 'title';

    #[Url(history: true)]
    public $sortDir = 'ASC';

    #[Url(history: true)]
    public $perPage = 10;

    #[On('book-changed')]
    public function refreshBooks($message)
    {
        session()->flash($message['type'], $message['content']);
    }

    #[On('rental-changed')]
    public function refreshBookRentals($message)
    {
        session()->flash($message['type'], $message['content']);
    }

    public function setSortBy($sortByField)
    {

        if ($this->sortBy === $sortByField) {
            $this->sortDir = ($this->sortDir == "ASC") ? 'DESC' : "ASC";
            return;
        }

        $this->sortBy = $sortByField;
        $this->sortDir = 'DESC';
    }

    public function render()
    {
        return view('livewire.book.book-list', [
            'books' => Book::search($this->search)
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate($this->perPage)
        ]);
    }
}
