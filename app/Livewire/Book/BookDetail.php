<?php

namespace App\Livewire\Book;

use App\Models\Book;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

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

    public function mount($id)
    {
        $this->book = Book::with('bookCopies', 'rentals')->find($id);
        $this->bookId = $id;
        $this->title = $this->book->title;
        $this->author = $this->book->author;
        $this->publisher = $this->book->publisher;
        $this->class = $this->book->class;
        $this->category = $this->book->category;
        $this->copies = $this->book->copies;
    }

    public function render()
    {
        return view('livewire.book.book-detail');
    }
}
