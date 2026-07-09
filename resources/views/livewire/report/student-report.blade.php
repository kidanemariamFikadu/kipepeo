<div>
    <div class="flex items-center justify-between mb-4 no-print">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-white">
            Enrollment Summary
        </h2>
        <button type="button" onclick="window.print()"
            class="inline-flex items-center p-2 px-4 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-sm">
            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" />
            </svg>
            Print
        </button>
    </div>

    <div class="printable">
        <x-report.print-header title="Enrollment Summary"
            :subtitle="$search ? 'Filtered by: ' . $search : null" />

        <div class="relative">
            <div wire:loading.class="opacity-40 pointer-events-none" wire:target="search, perPage"
                class="transition-opacity grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                        <div class="flex items-center justify-between p-4 no-print">
                            <div class="relative w-full max-w-xs">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400"
                                        fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                        <path fill-rule="evenodd"
                                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <input wire:model.live.debounce.300ms="search" type="text"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2"
                                    placeholder="Search schools">
                                <div wire:loading wire:target="search" class="absolute inset-y-0 right-3 flex items-center">
                                    <x-spinner class="h-4 w-4" />
                                </div>
                            </div>
                        </div>
                        <div class="overflow-x-auto no-print">
                            <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-4 py-3">Name</th>
                                        <th scope="col" class="px-4 py-3">Total students</th>
                                        <th scope="col" class="px-4 py-3">Current students</th>
                                        <th scope="col" class="px-4 py-3">Male</th>
                                        <th scope="col" class="px-4 py-3">Female</th>
                                        <th scope="col" class="px-4 py-3">Other</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($schoolReport as $school)
                                        <tr wire:key="{{ $school->id }}" class="border-b dark:border-gray-700">
                                            <th scope="row"
                                                class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                {{ $school->name }}</th>
                                            <td class="px-4 py-3">{{ $school->total_students }}</td>
                                            <td class="px-4 py-3">{{ $school->current_students }}</td>
                                            <td class="px-4 py-3">{{ $school->male_students_count }}</td>
                                            <td class="px-4 py-3">{{ $school->female_students_count }}</td>
                                            <td class="px-4 py-3">{{ $school->other_students_count }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6"
                                                class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                                No schools found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Print only: every matching school, not just the current page -->
                        <div class="hidden print:block">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3">Name</th>
                                        <th scope="col" class="px-4 py-3">Total students</th>
                                        <th scope="col" class="px-4 py-3">Current students</th>
                                        <th scope="col" class="px-4 py-3">Male</th>
                                        <th scope="col" class="px-4 py-3">Female</th>
                                        <th scope="col" class="px-4 py-3">Other</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($fullSchoolReport as $school)
                                        <tr class="border-b">
                                            <th scope="row" class="px-4 py-3 font-medium whitespace-nowrap">
                                                {{ $school->name }}</th>
                                            <td class="px-4 py-3">{{ $school->total_students }}</td>
                                            <td class="px-4 py-3">{{ $school->current_students }}</td>
                                            <td class="px-4 py-3">{{ $school->male_students_count }}</td>
                                            <td class="px-4 py-3">{{ $school->female_students_count }}</td>
                                            <td class="px-4 py-3">{{ $school->other_students_count }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-6 text-center">No schools found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            <p class="text-xs px-3 py-2">{{ $fullSchoolReport->count() }} schools total.</p>
                        </div>

                        <div class="py-4 px-3 no-print">
                            <div class="flex ">
                                <div class="flex space-x-4 items-center mb-3">
                                    <label class="w-32 text-sm font-medium text-gray-900 dark:text-gray-300">Per
                                        Page</label>
                                    <select wire:model.live='perPage'
                                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 ">
                                        <option value="5">5</option>
                                        <option value="7">7</option>
                                        <option value="10">10</option>
                                        <option value="20">20</option>
                                        <option value="50">50</option>
                                        <option value="100">100</option>
                                    </select>
                                </div>
                            </div>
                            {{ $schoolReport->links() }}
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-md shadow-md no-print">
                    <h2 class="text-gray-700 dark:text-white text-lg font-semibold mb-4">Gender split (this page)</h2>
                    <div wire:ignore x-data="{
                        chart: null,
                        render(data) {
                            if (this.chart) { this.chart.destroy(); }
                            this.chart = KipepeoCharts.pie(this.$refs.canvas, { labels: data.labels, data: data.data });
                        },
                    }"
                        x-init="render(@js(['labels' => ['Male', 'Female', 'Other'], 'data' => [$schoolReport->sum('male_students_count'), $schoolReport->sum('female_students_count'), $schoolReport->sum('other_students_count')]]))"
                        wire:key="gender-chart-{{ $schoolReport->sum('male_students_count') }}-{{ $schoolReport->sum('female_students_count') }}-{{ $schoolReport->sum('other_students_count') }}"
                        class="h-56">
                        <canvas x-ref="canvas" role="img"
                            aria-label="Pie chart of male, female, and other student counts on this page"></canvas>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Totals reflect the schools shown on
                        this page, not all schools.</p>
                </div>
            </div>

            <div wire:loading wire:target="search, perPage"
                class="no-print absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/60 dark:bg-gray-900/60">
                <x-spinner class="h-8 w-8" />
            </div>
        </div>
    </div>
</div>
