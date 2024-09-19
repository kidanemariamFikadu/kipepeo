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
    public $student;
    public $dueDate;
    public $rental;

    public function mount($bookId)
    {
        $this->bookId = $bookId;
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
    }

    public function render()
    {
        return view('livewire.book.rent', [
            'book' => Book::find($this->bookId),
            'students' => $this->getStudentsWithThreeConsecutiveDays()
        ]);
    }

    public function getStudentsWithThreeConsecutiveDays()
    {
        // Calculate the start and end dates of the previous week
        $endOfLastWeek = Carbon::now();
        $startOfLastWeek = Carbon::now()->subDays(6);

        // Fetch attendance records for the previous week
        $attendanceRecords = Attendance::whereBetween('date', [$startOfLastWeek, $endOfLastWeek])
            ->orderBy('student_id')
            ->orderBy('date')
            ->get();

        // Group records by student and sort by date
        $attendanceDict = [];
        foreach ($attendanceRecords as $record) {
            $attendanceDict[$record->student_id][] = Carbon::parse($record->date);
        }

        // Check for three consecutive days
        $studentsWithThreeConsecutiveDays = [];
        foreach ($attendanceDict as $studentId => $dates) {
            if ($this->hasThreeConsecutiveDays($dates)) {
                $studentsWithThreeConsecutiveDays[] = $studentId;
            }
        }

        // Fetch student objects
        $students = Student::whereIn('id', $studentsWithThreeConsecutiveDays)->get();

        return $students;
    }

    private function hasThreeConsecutiveDays($dates)
    {
        $dates = collect($dates)->sort()->values();
        for ($i = 0; $i <= count($dates) - 3; $i++) {
            if (
                $dates[$i + 1]->diffInDays($dates[$i]) == 1 &&
                $dates[$i + 2]->diffInDays($dates[$i + 1]) == 1
            ) {
                return true;
            }
        }
        return false;
    }
}
