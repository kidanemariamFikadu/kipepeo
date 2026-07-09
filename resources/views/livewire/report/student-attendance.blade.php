<div class="p-2 md:p-6">
    <div class="flex items-center justify-between mb-4 no-print">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-white">
            Daily Attendance Roster
        </h2>
    </div>

    <!-- Date Filter Form -->
    <form wire:submit.prevent="getStudentByDate" class="mb-6 no-print">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-400">Date</label>
                <input type="date" id="date" wire:model="date"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                @error('date')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
            <div class="flex items-center space-x-2">
                <button type="submit" wire:loading.attr="disabled" wire:target="getStudentByDate"
                    class="inline-flex items-center p-2 px-4 bg-primary-700 hover:bg-primary-800 text-white rounded-lg text-sm disabled:opacity-50">
                    <span wire:loading.remove wire:target="getStudentByDate">Filter</span>
                    <span wire:loading wire:target="getStudentByDate" class="inline-flex items-center">
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
        <div wire:loading.class="opacity-40 pointer-events-none" wire:target="getStudentByDate"
            class="transition-opacity">
        <x-report.print-header title="Daily Attendance Roster"
            :subtitle="\Carbon\Carbon::parse($date)->format('l, M j, Y')" />

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                    Students Present ({{ $students->count() }})
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">School</th>
                            <th class="px-4 py-3">Guardian</th>
                            <th class="px-4 py-3">Guardian Phone</th>
                            <th class="px-4 py-3">Time Stayed</th>
                            <th class="px-4 py-3">Sign In/Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($students as $student)
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400">{{ $loop->iteration }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                    {{ $student['name'] }}</td>
                                <td class="px-4 py-3">{{ $student['school'] }}</td>
                                <td class="px-4 py-3">
                                    {{ $student['guardians']->pluck('guardian_name')->filter()->implode(', ') ?: '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ $student['guardians']->pluck('guardian_phone')->filter()->implode(', ') ?: '—' }}
                                </td>
                                <td class="px-4 py-3">{{ $student['total_time'] }}</td>
                                <td class="px-4 py-3">
                                    @if ($student['attributes']->isNotEmpty())
                                        {{ $student['attributes']->map(fn($attr) => $attr['time_in'] . '–' . ($attr['time_out'] ?: 'Still in'))->implode(', ') }}
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                    No students found for the selected date.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        </div>

        <div wire:loading wire:target="getStudentByDate"
            class="no-print absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/60 dark:bg-gray-900/60">
            <x-spinner class="h-8 w-8" />
        </div>
    </div>
</div>
