<div class="bg-white dark:bg-gray-800 p-6 rounded-md shadow-md">
    <h2 class="text-gray-700 dark:text-white text-xl font-semibold mb-4">Library</h2>

    <div class="grid grid-cols-2 gap-3 mb-4">
        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-900">
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalBooks }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Titles</p>
        </div>
        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-900">
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalCopies }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Total copies</p>
        </div>
        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-900">
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $activeRentals }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Currently on loan</p>
        </div>
        <div class="p-3 rounded-lg {{ $overdueRentals > 0 ? 'bg-red-50 dark:bg-red-950' : 'bg-gray-50 dark:bg-gray-900' }}">
            <p class="text-2xl font-semibold {{ $overdueRentals > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                {{ $overdueRentals }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Overdue</p>
        </div>
    </div>

    <div wire:ignore x-data="{ chart: null }"
        x-init="chart = KipepeoCharts.stackedBar($refs.canvas, @js($chart))" class="h-24">
        <canvas x-ref="canvas" role="img" aria-label="Stacked bar chart of book copy status: available, lost, stolen"></canvas>
    </div>
</div>
