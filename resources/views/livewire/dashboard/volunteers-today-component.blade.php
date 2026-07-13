<div class="bg-white dark:bg-gray-800 p-6 rounded-md shadow-md">
    <h2 class="text-gray-700 dark:text-white text-xl font-semibold mb-4">Volunteers</h2>

    <div class="grid grid-cols-3 gap-3">
        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-900">
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $volunteersToday }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Here today</p>
        </div>
        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-900">
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $volunteersCurrentlyIn }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Currently in</p>
        </div>
        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-900">
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalHoursToday }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Hours today</p>
        </div>
    </div>
</div>
