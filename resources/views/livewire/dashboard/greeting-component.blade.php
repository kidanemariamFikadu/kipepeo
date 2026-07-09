<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700 col-span-2 sm:col-span-1">
        <div class="flex items-center">
            @if ($greeting == 'Good Morning')
                <svg class="h-5 w-5 text-red-500 shrink-0" viewBox="0 0 24 24" width="24" height="24"
                    xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8h1a4 4 0 0 1 0 8h-1" />
                    <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z" />
                    <line x1="6" y1="1" x2="6" y2="4" />
                    <line x1="10" y1="1" x2="10" y2="4" />
                    <line x1="14" y1="1" x2="14" y2="4" />
                </svg>
            @elseif($greeting == 'Good Afternoon')
                <svg class="h-5 w-5 text-red-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="5" />
                    <line x1="12" y1="1" x2="12" y2="3" />
                    <line x1="12" y1="21" x2="12" y2="23" />
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64" />
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78" />
                    <line x1="1" y1="12" x2="3" y2="12" />
                    <line x1="21" y1="12" x2="23" y2="12" />
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36" />
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22" />
                </svg>
            @elseif ($greeting == 'Good Evening')
                <svg class="h-5 w-5 text-red-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 18a5 5 0 0 0-10 0" />
                    <line x1="12" y1="9" x2="12" y2="2" />
                    <line x1="4.22" y1="10.22" x2="5.64" y2="11.64" />
                    <line x1="1" y1="18" x2="3" y2="18" />
                    <line x1="21" y1="18" x2="23" y2="18" />
                    <line x1="18.36" y1="11.64" x2="19.78" y2="10.22" />
                    <line x1="23" y1="22" x2="1" y2="22" />
                    <polyline points="16 5 12 9 8 5" />
                </svg>
            @endif
            <span class="text-gray-700 dark:text-white ml-2 font-medium">{{ $greeting }}, {{ $name }}</span>
        </div>
    </div>

    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
        <p class="text-3xl font-semibold text-gray-900 dark:text-white">{{ $studentsInAttendanceToday }}</p>
        <div class="flex items-center justify-between mt-1">
            <p class="text-xs text-gray-500 dark:text-gray-400">Currently in</p>
            @if ($studentsInAttendanceToday > 0)
                <button title="Check out all students"
                    wire:loading.attr="disabled" wire:target="checkOut"
                    class="focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 rounded"
                    wire:click="checkOut()" wire:confirm="Check out all students currently in attendance?">
                    <svg class="h-4 w-4 dark:text-red-300 text-red-600" width="24" height="24" viewBox="0 0 24 24"
                        stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round"
                        stroke-linejoin="round" wire:loading.remove wire:target="checkOut">
                        <path stroke="none" d="M0 0h24v24H0z" />
                        <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" />
                        <path d="M7 12h14l-3 -3m0 6l3 -3" />
                    </svg>
                    <x-spinner class="h-4 w-4" wire:loading wire:target="checkOut" />
                </button>
            @endif
        </div>
    </div>

    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
        <p class="text-3xl font-semibold text-gray-900 dark:text-white">{{ $totalStudents }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Attended today</p>
    </div>

    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
        <p class="text-3xl font-semibold text-gray-900 dark:text-white">{{ $totalEnrolled }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Enrolled students</p>
    </div>

    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
        <p class="text-3xl font-semibold text-gray-900 dark:text-white">{{ $averageAttendanceDuration }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Avg stay today</p>
    </div>
</div>
