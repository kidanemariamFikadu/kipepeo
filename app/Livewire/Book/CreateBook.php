<?php

namespace App\Livewire\Book;

use App\Models\Book;
use App\Models\BookCopy;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class CreateBook  extends ModalComponent
{
    public $title;
    public $author;
    public $publisher;
    public $class;
    public $category;
    public $copies;

    public Book $book;
    public $bookId;

    public function mount($bookId = null)
    {
        $this->bookId = $bookId;
        if ($bookId) {
            $book = Book::find($bookId);
            $this->title = $book->title;
            $this->author = $book->author;
            $this->publisher = $book->publisher;
            $this->class = $book->class;
            $this->category = $book->category;
            $this->copies = $book->copies;
        }
    }

    function create()
    {
        $this->validate([
            'title' => 'required',
            'author' => 'required',
            'category' => 'required',
            'copies' => 'required|integer|min:1',
        ]);

        if (!$this->bookId) {
            $book = Book::create([
                'title' => $this->title,
                'author' => $this->author,
                'publisher' => $this->publisher,
                'class' => $this->class,
                'category' => $this->category,
                'copies' => $this->copies,
            ]);

            for ($i = 1; $i <= $this->copies; $i++) {
                BookCopy::create([
                    'book_id' => $book->id,
                    'status' => 'available',
                ]);
            }
            $this->dispatch('book-changed', ['type' => 'success', 'content' => 'Book created successfully', 'book' => $book]);
        } else {
            $book = Book::find($this->bookId);
            $book->update([
                'title' => $this->title,
                'author' => $this->author,
                'publisher' => $this->publisher,
                'class' => $this->class,
                'category' => $this->category,
            ]);
            $this->dispatch('book-changed', ['type' => 'success', 'content' => 'Book updated successfully', 'book' => $book]);
        }
        $this->reset();

        $this->closeModal();
    }
    public function render()
    {
        return view('livewire.book.create-book');
    }
}
