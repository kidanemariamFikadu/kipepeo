<?php

namespace App\Livewire\Book;

use App\Models\Attendance;
use App\Models\Book;
use App\Models\Rental;
use App\Models\Student;
use Carbon\Carbon;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;

class Rent extends ModalComponent
{
    public $bookId;
    public $bookSearch = '';
    public $student;
    public $dueDate;
    public $rental;

    public static function modalMaxWidth(): string
    {
        return '3xl';
    }

    public function mount($bookId = null)
    {
        $this->bookId = $bookId;
    }

    public function getSearchableBooksProperty()
    {
        return Book::search($this->bookSearch)->orderBy('title')->limit(10)->get();
    }

    public function selectBook($bookId)
    {
        $this->bookId = $bookId;
    }

    public function changeBook()
    {
        $this->bookId = null;
        $this->bookSearch = '';
    }

    public function rent()
    {
        $this->validate([
            'student' => 'required',
            'dueDate' => 'required|date|after:today'
        ]);

        $book = Book::find($this->bookId);

        if ($book->available_copies <= 0) {
            $this->reset();
            $this->closeModal();
            $this->dispatch('rental-changed', ['type' => 'error', 'content' => 'No available copies']);
            return;
        }

        $this->rental = Rental::create([
            'book_id' => $this->bookId,
            'user_id' => auth()->id(),
            'student_id' => $this->student,
            'rented_at' => Carbon::now(),
            'due_at' => Carbon::parse($this->dueDate)
        ]);

        $this->reset();
        $this->closeModal();

        $this->dispatch('rental-changed', ['type' => 'success', 'content' => 'Book rented successfully']);
        $this->dispatch('dashboard-changed', ['type' => 'success', 'content' => 'Book rented successfully']);
    }

    public function render()
    {
        return view('livewire.book.rent', [
            'book' => Book::find($this->bookId),
            'students' => $this->getRentableStudents()
        ]);
    }

    /**
     * Students eligible to borrow: anyone marked present today, plus
     * alumni -- graduated students no longer show up in attendance but
     * should still be able to rent.
     */
    public function getRentableStudents()
    {
        return Student::where(function ($query) {
            $query->whereHas('attendances', function ($query) {
                $query->whereDate('date', now());
            })->orWhereNotNull('graduated_at');
        })->orderBy('name')->get();
    }
}
