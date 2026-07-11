<div>
    <x-flash-toast />

    <div class="p-2 md:p-6 space-y-6">
        @livewire('dashboard.greeting-component', ['name' => auth()->user()->name])

        @php
            $quickLinks = [
                ['component' => 'attendance.quick-check-in-students', 'label' => 'Mark attendance', 'icon' => '<path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/>'],
                ['component' => 'attendance.quick-check-in-volunteers', 'label' => 'Check in a volunteer', 'icon' => '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>'],
                ['component' => 'student.create-student', 'label' => 'Add a student', 'icon' => '<path d="M12 5v14M5 12h14"/>'],
                ['component' => 'book.rent', 'label' => 'Rent a book', 'icon' => '<path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/>'],
            ];
        @endphp

        <div>
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Quick access</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                @foreach ($quickLinks as $link)
                    <button type="button"
                        wire:click="$dispatch('openModal', { component: '{{ $link['component'] }}' })"
                        class="flex items-center gap-3 bg-white dark:bg-gray-800 p-4 rounded-md shadow-md hover:ring-2 hover:ring-primary-500 text-left">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-200">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">{!! $link['icon'] !!}</svg>
                        </span>
                        <span class="text-sm font-semibold text-gray-700 dark:text-white">{{ $link['label'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <div>
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Today</h2>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-md shadow-md">
                    <h3 class="text-gray-700 dark:text-white text-xl font-semibold mb-4">Currently in</h3>
                    @livewire('dashboard.in-session-component')
                </div>

                <livewire:dashboard.volunteers-today-component />

                <div class="bg-white dark:bg-gray-800 p-6 rounded-md shadow-md">
                    <h3 class="text-gray-700 dark:text-white text-xl font-semibold mb-4">Birthdays this week</h3>
                    @livewire('dashboard.birthday-component')
                </div>
            </div>
        </div>

        <div>
            <h2 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-3">Trends &amp; overview</h2>
            <div class="space-y-4">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <livewire:dashboard.attendance-trend-chart />
                    <livewire:dashboard.student-breakdown-chart />
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <livewire:dashboard.book-inventory-chart />
                    <livewire:dashboard.alumni-stats-component />
                </div>

                <livewire:dashboard.attending-students-by-school />
            </div>
        </div>
    </div>
</div>
