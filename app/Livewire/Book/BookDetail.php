<?php

namespace App\Livewire\Book;

use App\Models\Book;
use App\Models\BookCopy;
use Livewire\Attributes\On;
use Livewire\Component;

class BookDetail extends Component
{
    var Book $book;
    var $bookId;

    public $title;
    public $author;
    public $publisher;
    public $class;
    public $category;
    public $copies;

    #[On('rental-changed')]
    public function refreshRentals($message)
    {
        if ($message)
            session()->flash($message['type'], $message['content']);
    }

    public function mount($id)
    {
        $this->book = Book::with([
            'bookCopies',
            'rentals' => fn ($q) => $q->orderByDesc('rented_at')->limit(20),
            'rentals.checkedOutTo',
        ])->find($id);
        $this->bookId = $id;
        $this->title = $this->book->title;
        $this->author = $this->book->author;
        $this->publisher = $this->book->publisher;
        $this->class = $this->book->class;
        $this->category = $this->book->category;
        $this->copies = $this->book->copies;
    }

    public function update()
    {
        $this->validate([
            'title' => 'required',
            'author' => 'required',
            'category' => 'required',
            'copies' => 'required|integer|min:1',
        ]);

        $circulating = $this->book->bookCopies()->whereIn('status', ['borrowed', 'lost', 'stolen'])->count();

        if ($this->copies < $circulating) {
            $this->addError('copies', "Can't reduce copies below the {$circulating} currently borrowed, lost, or stolen.");
            return;
        }

        if ($this->copies > $this->book->copies) {
            for ($i = $this->book->copies; $i < $this->copies; $i++) {
                BookCopy::create([
                    'book_id' => $this->book->id,
                    'status' => 'available',
                ]);
            }
        } elseif ($this->copies < $this->book->copies) {
            $ids = BookCopy::where('book_id', $this->book->id)
                ->where('status', 'available')
                ->latest('id')
                ->take($this->book->copies - $this->copies)
                ->pluck('id');

            BookCopy::whereIn('id', $ids)->delete();
        }

        $this->book->update([
            'title' => $this->title,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'class' => $this->class,
            'category' => $this->category,
            'copies' => $this->copies,
        ]);

        session()->flash('success', 'Book updated successfully');
    }

    public function deleteBook()
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $this->book->delete();

        session()->flash('success', 'Book deleted successfully.');

        return $this->redirect(route('books'));
    }

    public function render()
    {
        return view('livewire.book.book-detail');
    }
}
