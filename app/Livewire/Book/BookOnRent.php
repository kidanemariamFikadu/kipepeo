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
     * title/author/publisher live on `books` and borrowed_by is the
     * student's name, so those four are resolved through a join in
     * render() rather than as columns on `rentals`.
     */
    protected function sortableColumns(): array
    {
        return ['due_at', 'returned_at', 'created_at', 'title', 'author', 'publisher', 'borrowed_by'];
    }

    /** Sortable columns that live on the related books row. */
    private const BOOK_SORTS = ['title', 'author', 'publisher'];

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

        $query->search($this->search);

        $sortBy = $this->safeSortBy();
        $sortDir = $this->safeSortDir();

        // Title, author, publisher and the borrower's name are not columns
        // on `rentals` -- sorting by them needs the related table joined in.
        // select() keeps the joined columns from overwriting the rental's own.
        if (in_array($sortBy, self::BOOK_SORTS, true)) {
            $query->leftJoin('books', 'books.id', '=', 'rentals.book_id')
                ->select('rentals.*')
                ->orderBy('books.'.$sortBy, $sortDir);
        } elseif ($sortBy === 'borrowed_by') {
            $query->leftJoin('students', 'students.id', '=', 'rentals.student_id')
                ->select('rentals.*')
                ->orderBy('students.name', $sortDir);
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        $bookOnRent = $query->paginate($this->perPage);
        return view('livewire.book.book-on-rent', [
            'booksOnRent' => $bookOnRent
        ]);
    }
}
