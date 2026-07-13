<?php

namespace App\Livewire\Report;

use App\Models\BookCopy;
use App\Models\Rental;
use Carbon\Carbon;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class BookRentalReport extends Component
{
    use WithPagination;

    public $fromDate;
    public $toDate;

    #[Url()]
    public $perPage = 10;

    public $totalRentals;
    public $returnedOnTime;
    public $returnedLate;
    public $avgDaysToReturn;
    public $topBooks = [];
    public $rentalsByCategory = [];

    public function mount()
    {
        $this->fromDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->toDate = Carbon::now()->format('Y-m-d');
        $this->filter();
    }

    public function filter()
    {
        $this->validate([
            'fromDate' => 'required|date',
            'toDate' => 'required|date|after_or_equal:fromDate',
        ]);
        $this->resetPage();

        $rentals = $this->rangeQuery()->get();

        $this->totalRentals = $rentals->count();

        $returned = $rentals->whereNotNull('returned_at');
        $this->returnedOnTime = $returned->filter(fn ($r) => Carbon::parse($r->returned_at)->lte(Carbon::parse($r->due_at)))->count();
        $this->returnedLate = $returned->count() - $this->returnedOnTime;
        $this->avgDaysToReturn = round($returned->avg(fn ($r) => Carbon::parse($r->rented_at)->diffInDays(Carbon::parse($r->returned_at), true)) ?? 0, 1);

        $this->topBooks = $rentals->groupBy(fn ($r) => $r->book?->title ?: 'Unknown')->map->count()->sortDesc()->take(10);
        $this->rentalsByCategory = $rentals->groupBy(fn ($r) => $r->book?->category ?: 'Uncategorized')->map->count()->sortDesc();
    }

    protected function rangeQuery()
    {
        return Rental::with(['book', 'checkedOutTo', 'checkedOutBy'])
            ->whereBetween('rented_at', [
                Carbon::parse($this->fromDate)->startOfDay(),
                Carbon::parse($this->toDate)->endOfDay(),
            ])
            ->orderByDesc('rented_at');
    }

    public function render()
    {
        return view('livewire.report.book-rental-report', [
            // Live, unfiltered by date range - "what's the state right now".
            'currentlyBorrowed' => Rental::whereNull('returned_at')->count(),
            'currentlyOverdue' => Rental::whereNull('returned_at')->where('due_at', '<', now())->count(),
            'inventoryTotals' => [
                'available' => BookCopy::where('status', 'available')->count(),
                'lost' => BookCopy::where('status', 'lost')->count(),
                'stolen' => BookCopy::where('status', 'stolen')->count(),
            ],
            // Range-scoped analytics.
            'totalRentals' => $this->totalRentals,
            'returnedOnTime' => $this->returnedOnTime,
            'returnedLate' => $this->returnedLate,
            'avgDaysToReturn' => $this->avgDaysToReturn,
            'topBooks' => $this->topBooks,
            'rentalsByCategory' => $this->rentalsByCategory,
            'rentals' => $this->rangeQuery()->paginate($this->perPage),
            // Printing must include every rental in range, not just the current page.
            'fullRentals' => $this->rangeQuery()->get(),
        ]);
    }
}
