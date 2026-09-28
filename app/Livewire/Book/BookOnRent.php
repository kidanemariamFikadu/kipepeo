<?php

namespace App\Livewire\Book;

use App\Livewire\Concerns\HasSortableColumns;
use App\Models\Rental;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BookOnRent extends Component
{
    use HasSortableColumns;
    use WithPagination;

    /**
     * Only the columns that exist on `rentals`. The table header also
     * offers title/author/publisher/borrowed_by/due_date, none of which are
     * columns here -- those have never sorted (they raise a SQL error) and
     * need joins to work. They are left out rather than left crashing.
     */
    protected function sortableColumns(): array
    {
        return ['due_at', 'returned_at', 'created_at'];
    }

    #[Url(history: true)]
    public $search;
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

        $bookOnRent = $query->search($this->search)->orderBy($this->safeSortBy(), $this->safeSortDir())->paginate($this->perPage);
        return view('livewire.book.book-on-rent', [
            'booksOnRent' => $bookOnRent
        ]);
    }
}
