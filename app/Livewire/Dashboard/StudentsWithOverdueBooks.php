<?php

namespace App\Livewire\Dashboard;

use App\Models\Rental;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class StudentsWithOverdueBooks extends Component
{
    /**
     * Enough to act on without crowding the dashboard; the full list lives
     * on the Books on Loan page.
     */
    private const LIMIT = 8;

    #[On('dashboard-changed')]
    public function refreshDashboard(): void
    {
        // Re-renders on the event; the query runs in render().
    }

    /**
     * ReturnBook dispatches this when a book is handed back, so the card
     * drops the row instead of showing a book that is no longer overdue.
     */
    #[On('rental-changed')]
    public function refreshAfterReturn($message = null): void
    {
    }

    public function render()
    {
        $overdue = Rental::overdue()
            // A soft-deleted student would otherwise appear as a blank row.
            ->whereHas('checkedOutTo')
            ->with(['book', 'checkedOutTo'])
            ->get();

        $today = now()->startOfDay();

        $students = $overdue
            ->groupBy('student_id')
            ->map(function ($rentals) use ($today) {
                $student = $rentals->first()->checkedOutTo;

                // Each book carries its own rental id so it can be returned
                // individually -- a student may be holding several.
                $books = $rentals
                    ->map(fn ($r) => [
                        'rentalId' => $r->id,
                        'title' => $r->book?->title ?? 'Unknown book',
                        'daysOverdue' => (int) Carbon::parse($r->due_at)->startOfDay()->diffInDays($today),
                    ])
                    ->sortByDesc('daysOverdue')
                    ->values();

                return [
                    'studentId' => $student->id,
                    'studentName' => $student->name,
                    'books' => $books,
                    'count' => $books->count(),
                    'daysOverdue' => $books->max('daysOverdue'),
                ];
            })
            // Longest overdue first -- those are the ones worth chasing.
            ->sortByDesc('daysOverdue')
            ->values();

        return view('livewire.dashboard.students-with-overdue-books', [
            'students' => $students->take(self::LIMIT),
            'totalStudents' => $students->count(),
            'totalBooks' => $overdue->count(),
        ]);
    }
}
