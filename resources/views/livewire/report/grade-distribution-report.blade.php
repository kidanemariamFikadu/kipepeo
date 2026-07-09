<div class="p-2 md:p-6">
    <div class="flex items-center justify-between mb-4 no-print">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-white">
            Grade Distribution
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
        <x-report.print-header title="Grade Distribution" subtitle="Currently enrolled students by grade" />

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Currently Enrolled Students</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalEnrolled }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Not Yet Assigned a Grade</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $unassignedCount }}</p>
                @if ($unassignedCount > 0)
                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">These students need a grade set
                        before they can be promoted.</p>
                @endif
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-2">Students per grade</h3>
            <div wire:ignore x-data="{
                chart: null,
                render(data) {
                    if (this.chart) { this.chart.destroy(); }
                    this.chart = KipepeoCharts.stackedBar(this.$refs.canvas, { labels: data.labels, datasets: data.datasets });
                },
            }"
                x-init="render(@js([
                    'labels' => $grades->pluck('grade'),
                    'datasets' => [
                        ['label' => 'Male', 'data' => $grades->pluck('male_students_count')],
                        ['label' => 'Female', 'data' => $grades->pluck('female_students_count')],
                    ],
                ]))" wire:key="grade-chart-{{ $grades->sum('total_students') }}" class="h-96">
                <canvas x-ref="canvas" role="img" aria-label="Stacked bar chart of male and female students per grade"></canvas>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden page-break">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-3">Grade</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Male</th>
                            <th class="px-4 py-3">Female</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($grades as $grade)
                            <tr class="border-b dark:border-gray-700 {{ $grade->total_students == 0 ? 'text-gray-400 dark:text-gray-600' : '' }}">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $grade->grade }}</td>
                                <td class="px-4 py-3">{{ $grade->total_students }}</td>
                                <td class="px-4 py-3">{{ $grade->male_students_count }}</td>
                                <td class="px-4 py-3">{{ $grade->female_students_count }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
