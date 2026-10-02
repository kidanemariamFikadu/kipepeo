<?php

namespace App\Livewire\Report;

use App\Models\BookCategory;
use App\Models\BookCopy;
use App\Models\Rental;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

#[Title('Book & Rental Circulation')]
class BookRentalReport extends Component
{
    use WithPagination;

    public $fromDate;
    public $toDate;
    public $categoryId = '';
    public $status = '';

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
            'categoryId' => 'nullable|exists:book_categories,id',
            'status' => 'nullable|in:on_time,late,out',
        ]);
        $this->resetPage();
    }

    /**
     * Derived in render() from the collection that the print table already
     * needs, rather than re-running the range query here: filter() and
     * render() were each issuing their own ->get() with the same eager
     * loads, so a filter change fetched the whole range twice.
     */
    private function computeStatistics(Collection $rentals): void
    {
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
        return Rental::with(['book.bookCategory', 'checkedOutTo', 'checkedOutBy'])
            ->whereBetween('rented_at', [
                Carbon::parse($this->fromDate)->startOfDay(),
                Carbon::parse($this->toDate)->endOfDay(),
            ])
            ->when($this->categoryId, fn ($q) => $q->whereHas('book', fn ($sq) => $sq->where('category_id', $this->categoryId)))
            ->when($this->status === 'out', fn ($q) => $q->whereNull('returned_at'))
            ->when($this->status === 'on_time', fn ($q) => $q->whereNotNull('returned_at')->whereColumn('returned_at', '<=', 'due_at'))
            ->when($this->status === 'late', fn ($q) => $q->whereNotNull('returned_at')->whereColumn('returned_at', '>', 'due_at'))
            ->orderByDesc('rented_at');
    }

    public function render()
    {
        // Printing must include every rental in range, not just the current
        // page -- and the summary cards are derived from the same set.
        $fullRentals = $this->rangeQuery()->get();
        $this->computeStatistics($fullRentals);

        return view('livewire.report.book-rental-report', [
            // Live, unfiltered by date range - "what's the state right now".
            'currentlyBorrowed' => Rental::whereNull('returned_at')->count(),
            'currentlyOverdue' => Rental::overdue()->count(),
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
            'fullRentals' => $fullRentals,
            'categories' => BookCategory::orderBy('name')->get(),
        ]);
    }
}
