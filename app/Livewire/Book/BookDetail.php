<?php

namespace App\Livewire\Book;

use App\Models\Book;
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

    public function update()
    {
        $this->validate([
            'title' => 'required',
            'author' => 'required',
            'category' => 'required',
        ]);

        $this->book->update([
            'title' => $this->title,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'class' => $this->class,
            'category' => $this->category,
        ]);

        session()->flash('success', 'Book updated successfully');
    }

    public function render()
    {
        return view('livewire.book.book-detail');
    }
}
