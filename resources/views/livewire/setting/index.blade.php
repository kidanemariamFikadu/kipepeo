<div>
    <x-flash-toast />

    <div class="p-2 md:p-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-white mb-4">Settings</h2>

        @php
            $cards = [
                [
                    'href' => route('settings-schools'),
                    'title' => 'Schools',
                    'description' => 'Manage the schools students belong to.',
                    'count' => $this->schoolCount . ' ' . Str::plural('school', $this->schoolCount),
                    'icon' => '<path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/>',
                ],
                [
                    'href' => route('settings-grades'),
                    'title' => 'Grades',
                    'description' => 'Grade levels and promotion order.',
                    'count' => $this->gradeCount . ' ' . Str::plural('grade', $this->gradeCount),
                    'icon' => '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 1.5 2.7 3 6 3s6-1.5 6-3v-5"/>',
                ],
                [
                    'href' => route('settings-volunteers'),
                    'title' => 'Volunteers',
                    'description' => 'Roster, rates, and status.',
                    'count' => $this->volunteerCount . ' ' . Str::plural('volunteer', $this->volunteerCount),
                    'icon' => '<path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>',
                ],
                [
                    'href' => route('settings-activity-types'),
                    'title' => 'Activity Types',
                    'description' => 'The duties volunteers log time against.',
                    'count' => $this->activityTypeCount . ' ' . Str::plural('duty', $this->activityTypeCount),
                    'icon' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>',
                ],
                [
                    'href' => route('settings-job-titles'),
                    'title' => 'Job titles',
                    'description' => 'Titles used across staff records.',
                    'count' => $this->jobTitleCount . ' ' . Str::plural('title', $this->jobTitleCount),
                    'icon' => '<path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
                ],
                [
                    'href' => route('promote-students'),
                    'title' => 'Promote Students',
                    'description' => 'Move a grade up at year end.',
                    'count' => 'End of term',
                    'icon' => '<path d="M12 5v14M5 12h14"/>',
                ],
                [
                    'href' => route('settings-import-students'),
                    'title' => 'Import Students',
                    'description' => 'Bulk-add students from a spreadsheet.',
                    'count' => 'CSV / XLSX',
                    'icon' => '<path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/>',
                ],
                [
                    'href' => route('settings-import-books'),
                    'title' => 'Import Books',
                    'description' => 'Bulk-add titles to the catalog.',
                    'count' => 'CSV / XLSX',
                    'icon' => '<path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/>',
                ],
            ];
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($cards as $card)
                <a href="{{ $card['href'] }}"
                    class="relative bg-white rounded-lg shadow dark:bg-gray-800 p-4 flex flex-col gap-2 hover:ring-2 hover:ring-primary-500">
                    <span class="flex h-9 w-9 items-center justify-center rounded-md bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-200">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">{!! $card['icon'] !!}</svg>
                    </span>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ $card['title'] }}</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $card['description'] }}</p>
                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $card['count'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>
