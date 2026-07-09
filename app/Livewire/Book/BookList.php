<?php

namespace App\Livewire\Book;

use App\Livewire\Concerns\HasSortableColumns;
use App\Models\Book;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use LivewireUI\Modal\ModalComponent;

class BookList extends Component
{
    use HasSortableColumns;
    use WithPagination;

    #[Url(history: true)]
    public $search = '';

    #[Url(history: true)]
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

    public function deleteBook($id)
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $book = Book::findOrFail($id);
        $book->delete();

        session()->flash('success', 'Book deleted successfully.');
    }

    public function render()
    {
        return view('livewire.book.book-list', [
            'books' => Book::search($this->search)
                ->withCount(['bookCopies as available_copies_count' => fn ($query) => $query->where('status', 'available')])
                ->orderBy($this->sortBy, $this->sortDir)
                ->paginate($this->perPage)
        ]);
    }
}
