<div class="bg-white dark:bg-gray-800 p-6 rounded-md shadow-md">
    <h2 class="text-gray-700 dark:text-white text-xl font-semibold mb-4">Alumni</h2>

    <div class="grid grid-cols-2 gap-3">
        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-900">
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $totalAlumni }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Total graduates</p>
        </div>
        <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-900">
            <p class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $graduatedThisYear }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">Graduated this year</p>
        </div>
    </div>
</div>
