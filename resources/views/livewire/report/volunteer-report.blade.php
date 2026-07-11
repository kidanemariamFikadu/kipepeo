<div class="p-2 md:p-6">
    <div class="flex items-center justify-between mb-4 no-print">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-white">
            Volunteer Activity Report
        </h2>
    </div>

    <!-- Filter Form -->
    <form wire:submit.prevent="filter" class="mb-6 no-print">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label for="fromDate" class="block text-sm font-medium text-gray-700 dark:text-gray-400">From</label>
                <input type="date" id="fromDate" wire:model="fromDate"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                @error('fromDate')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="toDate" class="block text-sm font-medium text-gray-700 dark:text-gray-400">To</label>
                <input type="date" id="toDate" wire:model="toDate"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                @error('toDate')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="volunteerId" class="block text-sm font-medium text-gray-700 dark:text-gray-400">Volunteer</label>
                <select id="volunteerId" wire:model="volunteerId"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">All volunteers</option>
                    @foreach ($volunteers as $volunteer)
                        <option value="{{ $volunteer->id }}">{{ $volunteer->name }}</option>
                    @endforeach
                </select>
                @error('volunteerId')
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

    <div class="printable">
        <x-report.print-header title="Volunteer Activity Report" subtitle="Hours and activities logged by volunteers" />

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Hours</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $this->secondsToHms($totalHoursSeconds) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Activities Logged</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalActivities }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Volunteers Active in Range</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $volunteersActive }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden page-break mb-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-white px-4 pt-4">Hours by Volunteer</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Volunteer</th>
                            <th class="px-4 py-3">Days Volunteered</th>
                            <th class="px-4 py-3">Total Hours</th>
                            <th class="px-4 py-3">Est. Stipend</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($hoursByVolunteer as $row)
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $row['volunteer']?->name ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $row['visits'] }}</td>
                                <td class="px-4 py-3">{{ $this->secondsToHms($row['totalSeconds']) }}</td>
                                <td class="px-4 py-3">{{ $row['estStipend'] !== null ? 'KSH ' . number_format($row['estStipend'], 2) : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                    No volunteer visits found for the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden page-break mb-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-white px-4 pt-4">Activities by Type</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Duty</th>
                            <th class="px-4 py-3">Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activityCountsByType as $row)
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $row['activityType']?->name ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $row['count'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                    No activities found for the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($volunteerId)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden page-break">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-white px-4 pt-4">Activity Log</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Activity Type</th>
                                <th class="px-4 py-3">Students</th>
                                <th class="px-4 py-3">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($activityLog as $activity)
                                <tr wire:key="{{ $activity->id }}" class="border-b dark:border-gray-700">
                                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($activity->date)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3">{{ $activity->activityType?->name ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $activity->students->pluck('name')->implode(', ') ?: '—' }}</td>
                                    <td class="px-4 py-3">{{ $activity->notes ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                        No activities logged for this volunteer in range.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
