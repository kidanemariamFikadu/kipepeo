<div class="p-2 md:p-6">
    <div class="flex items-center justify-between mb-4 no-print">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-white">
            Alumni Report
        </h2>
    </div>

    <!-- Filter Form -->
    <form wire:submit.prevent="filter" class="mb-6 no-print">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label for="fromDate" class="block text-sm font-medium text-gray-700 dark:text-gray-400">Graduated
                    From</label>
                <input type="date" id="fromDate" wire:model="fromDate"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                @error('fromDate')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="toDate" class="block text-sm font-medium text-gray-700 dark:text-gray-400">Graduated
                    To</label>
                <input type="date" id="toDate" wire:model="toDate"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                @error('toDate')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="gradeId" class="block text-sm font-medium text-gray-700 dark:text-gray-400">Graduated
                    From Grade</label>
                <select id="gradeId" wire:model="gradeId"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">All grades</option>
                    @foreach ($grades as $grade)
                        <option value="{{ $grade->id }}">{{ $grade->grade }}</option>
                    @endforeach
                </select>
                @error('gradeId')
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
        <x-report.print-header title="Alumni Report" subtitle="Students who have completed their final grade" />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Alumni</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalAlumni }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden page-break">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Gender</th>
                            <th class="px-4 py-3">Graduated From</th>
                            <th class="px-4 py-3">School</th>
                            <th class="px-4 py-3">Graduation Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($alumni as $student)
                            <tr wire:key="{{ $student->id }}" class="border-b dark:border-gray-700">
                                <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $student->name }}</td>
                                <td class="px-4 py-3">{{ ucfirst((string) $student->gender) }}</td>
                                <td class="px-4 py-3">{{ $student->graduatedGrade?->grade ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $student->schools->first()?->school?->name ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $student->graduated_at->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                    No alumni found for the selected filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
