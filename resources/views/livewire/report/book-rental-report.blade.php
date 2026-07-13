<div class="p-2 md:p-6">
    <div class="flex items-center justify-between mb-4 no-print">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-white">
            Book &amp; Rental Circulation
        </h2>
    </div>

    <!-- Date Filter Form -->
    <form wire:submit.prevent="filter" class="mb-6 no-print">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label for="fromDate" class="block text-sm font-medium text-gray-700 dark:text-gray-400">From
                    Date</label>
                <input type="date" id="fromDate" wire:model="fromDate"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                @error('fromDate')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="toDate" class="block text-sm font-medium text-gray-700 dark:text-gray-400">To
                    Date</label>
                <input type="date" id="toDate" wire:model="toDate"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                @error('toDate')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
            <div class="flex items-center space-x-2">
                <button type="submit" wire:loading.attr="disabled" wire:target="filter"
                    class="inline-flex items-center p-2 px-4 bg-primary-700 hover:bg-primary-800 text-white rounded-lg text-sm disabled:opacity-50">
                    <span wire:loading.remove wire:target="filter">Filter</span>
                    <span wire:loading wire:target="filter" class="inline-flex items-center">
                        <x-spinner class="h-4 w-4 mr-1.5 text-white" />
                        Filtering…
                    </span>
                </button>
                <button type="button" onclick="window.print()"
                    class="inline-flex items-center p-2 px-4 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-sm">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" />
                    </svg>
                    Print
                </button>
            </div>
        </div>
    </form>

    <div class="printable relative">
        <x-report.print-header title="Book &amp; Rental Circulation"
            :subtitle="\Carbon\Carbon::parse($fromDate)->format('M j, Y') . ' – ' . \Carbon\Carbon::parse($toDate)->format('M j, Y')" />

        <!-- Live snapshot, independent of the date range above -->
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Currently Borrowed</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $currentlyBorrowed }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Currently Overdue</p>
                <p class="text-2xl font-bold {{ $currentlyOverdue > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                    {{ $currentlyOverdue }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Available Copies</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $inventoryTotals['available'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Lost Copies</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $inventoryTotals['lost'] }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Stolen Copies</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $inventoryTotals['stolen'] }}</p>
            </div>
        </div>

        <div wire:loading.class="opacity-40 pointer-events-none" wire:target="filter, perPage" class="transition-opacity">
            <!-- Range-scoped summary -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Rentals in Range</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalRentals }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Returned on Time</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $returnedOnTime }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Returned Late</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $returnedLate }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Avg. Days to Return</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $avgDaysToReturn }}</p>
                </div>
            </div>

            @if ($totalRentals == 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-8 text-center text-gray-500 dark:text-gray-400">
                    No rentals found for the selected date range.
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6 no-print">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-2">Most Borrowed Books</h3>
                        <div wire:ignore x-data="{
                            chart: null,
                            render(data) {
                                if (this.chart) { this.chart.destroy(); }
                                this.chart = KipepeoCharts.bar(this.$refs.canvas, { labels: data.labels, data: data.data, horizontal: true });
                            },
                        }"
                            x-init="render(@js(['labels' => $topBooks->keys(), 'data' => $topBooks->values()]))"
                            wire:key="top-books-chart-{{ $topBooks->sum() }}-{{ $fromDate }}-{{ $toDate }}"
                            class="relative h-64">
                            <canvas x-ref="canvas" role="img" aria-label="Most borrowed books"></canvas>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-2">Rentals by Category</h3>
                        <div wire:ignore x-data="{
                            chart: null,
                            render(data) {
                                if (this.chart) { this.chart.destroy(); }
                                this.chart = KipepeoCharts.bar(this.$refs.canvas, { labels: data.labels, data: data.data, horizontal: true, seriesIndex: 1 });
                            },
                        }"
                            x-init="render(@js(['labels' => $rentalsByCategory->keys(), 'data' => $rentalsByCategory->values()]))"
                            wire:key="category-chart-{{ $rentalsByCategory->sum() }}-{{ $fromDate }}-{{ $toDate }}"
                            class="relative h-64">
                            <canvas x-ref="canvas" role="img" aria-label="Rentals by category"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Detail table (screen: paginated) -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden page-break no-print">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Book</th>
                                    <th class="px-4 py-3">Student</th>
                                    <th class="px-4 py-3">Rented</th>
                                    <th class="px-4 py-3">Due</th>
                                    <th class="px-4 py-3">Returned</th>
                                    <th class="px-4 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($rentals as $rental)
                                    <tr wire:key="rental-{{ $rental->id }}" class="border-b dark:border-gray-700">
                                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                            {{ $rental->book?->title ?? 'Deleted book' }}</td>
                                        <td class="px-4 py-3">{{ $rental->checkedOutTo?->name ?? 'Deleted student' }}
                                        </td>
                                        <td class="px-4 py-3">{{ \Carbon\Carbon::parse($rental->rented_at)->format('M j, Y') }}</td>
                                        <td class="px-4 py-3">{{ \Carbon\Carbon::parse($rental->due_at)->format('M j, Y') }}</td>
                                        <td class="px-4 py-3">
                                            {{ $rental->returned_at ? \Carbon\Carbon::parse($rental->returned_at)->format('M j, Y') : '—' }}
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($rental->returned_at)
                                                <span
                                                    class="px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">Returned</span>
                                            @elseif (\Carbon\Carbon::parse($rental->due_at)->isPast())
                                                <span
                                                    class="px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">Overdue</span>
                                            @else
                                                <span
                                                    class="px-2 py-1 rounded text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">Borrowed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="py-4 px-3">
                        <div class="flex mb-3">
                            <div class="flex space-x-4 items-center">
                                <label class="w-24 text-sm font-medium text-gray-900 dark:text-gray-300">Per
                                    Page</label>
                                <select wire:model.live='perPage'
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5">
                                    <option value="10">10</option>
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                        {{ $rentals->links() }}
                    </div>
                </div>

                <!-- Print only: every rental in range, not just the current page -->
                <div class="hidden print:block">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase bg-gray-50">
                            <tr>
                                <th class="px-4 py-3">Book</th>
                                <th class="px-4 py-3">Student</th>
                                <th class="px-4 py-3">Rented</th>
                                <th class="px-4 py-3">Due</th>
                                <th class="px-4 py-3">Returned</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($fullRentals as $rental)
                                <tr class="border-b">
                                    <td class="px-4 py-3 font-medium">{{ $rental->book?->title ?? 'Deleted book' }}</td>
                                    <td class="px-4 py-3">{{ $rental->checkedOutTo?->name ?? 'Deleted student' }}</td>
                                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($rental->rented_at)->format('M j, Y') }}</td>
                                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($rental->due_at)->format('M j, Y') }}</td>
                                    <td class="px-4 py-3">
                                        {{ $rental->returned_at ? \Carbon\Carbon::parse($rental->returned_at)->format('M j, Y') : '—' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ $rental->returned_at ? 'Returned' : (\Carbon\Carbon::parse($rental->due_at)->isPast() ? 'Overdue' : 'Borrowed') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p class="text-xs px-3 py-2">{{ $fullRentals->count() }} rentals total.</p>
                </div>
            @endif
        </div>

        <div wire:loading wire:target="filter, perPage"
            class="no-print absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/60 dark:bg-gray-900/60">
            <x-spinner class="h-8 w-8" />
        </div>
    </div>
</div>
