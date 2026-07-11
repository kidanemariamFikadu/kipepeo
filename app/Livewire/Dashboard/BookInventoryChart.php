<?php

namespace App\Livewire\Dashboard;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Rental;
use Livewire\Attributes\On;
use Livewire\Component;

class BookInventoryChart extends Component
{
    #[On('dashboard-changed')]
    public function refreshDashboard()
    {
    }

    public function render()
    {
        $statusCounts = BookCopy::query()
            ->whereIn('book_id', Book::query()->pluck('id'))
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('livewire.dashboard.book-inventory-chart', [
            'chart' => [
                'labels' => ['Copies'],
                'datasets' => [
                    ['label' => 'Available', 'data' => [(int) ($statusCounts['available'] ?? 0)]],
                    ['label' => 'Lost', 'data' => [(int) ($statusCounts['lost'] ?? 0)]],
                    ['label' => 'Stolen', 'data' => [(int) ($statusCounts['stolen'] ?? 0)]],
                ],
            ],
            'totalBooks' => Book::count(),
            'totalCopies' => BookCopy::whereIn('book_id', Book::query()->pluck('id'))->count(),
            'activeRentals' => Rental::whereNull('returned_at')->count(),
            'overdueRentals' => Rental::whereNull('returned_at')->where('due_at', '<', now())->count(),
        ]);
    }
}
