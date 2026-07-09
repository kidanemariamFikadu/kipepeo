<div>
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-3 rounded relative" role="alert">
            <strong class="font-bold">Error</strong>
            <span class="block sm:inline">{{ session('error') }}</span>

        </div>
    @elseif (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 mb-3 rounded relative" role="alert">
            <strong class="font-bold">Success</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="p-2 md:p-6 space-y-4">
        @livewire('dashboard.greeting-component', ['name' => auth()->user()->name])

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <livewire:dashboard.attendance-trend-chart />
            <livewire:dashboard.student-breakdown-chart />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-md shadow-md">
                <h2 class="text-gray-700 dark:text-white text-xl font-semibold mb-4">Currently in</h2>
                @livewire('dashboard.in-session-component')
            </div>

            <div class="bg-white dark:bg-gray-800 p-6 rounded-md shadow-md">
                <h2 class="text-gray-700 dark:text-white text-xl font-semibold mb-4">Birthdays this week</h2>
                @livewire('dashboard.birthday-component')
            </div>

            <livewire:dashboard.book-inventory-chart />
        </div>

        <div class="grid grid-cols-1 gap-4">
            <div>
                <livewire:dashboard.attending-students-by-school />
            </div>
        </div>
    </div>
</div>
