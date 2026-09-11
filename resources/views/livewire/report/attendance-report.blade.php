<div class="p-2 md:p-6">
    <div class="flex items-center justify-between mb-4 no-print">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-white">
            Attendance Analytics
        </h2>
    </div>

    <!-- Date Filter Form -->
    <form wire:submit.prevent="filter" class="mb-6 no-print">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label for="fromDate" class="block text-sm font-medium text-gray-700 dark:text-gray-400">From
                    Date</label>
                <input type="date" id="fromDate" wire:model="fromDate"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                @error('fromDate')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="toDate" class="block text-sm font-medium text-gray-700 dark:text-gray-400">To
                    Date</label>
                <input type="date" id="toDate" wire:model="toDate"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                @error('toDate')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="studentId" class="block text-sm font-medium text-gray-700 dark:text-gray-400">Student</label>
                <select id="studentId" wire:model="studentId"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">All students</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}">{{ $student->name }}</option>
                    @endforeach
                </select>
                @error('studentId')
                    <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="perPage" class="block text-sm font-medium text-gray-700 dark:text-gray-400">Rows per
                    page</label>
                <select id="perPage" wire:model.live="perPage"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
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

    <div class="printable relative">
        <div wire:loading.class="opacity-40 pointer-events-none" wire:target="filter" class="transition-opacity">
        <x-report.print-header title="Attendance Analytics"
            :subtitle="\Carbon\Carbon::parse($fromDate)->format('M j, Y') . ' – ' . \Carbon\Carbon::parse($toDate)->format('M j, Y')" />

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Attendance Records</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $totalStudents }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">Average Time Spent</p>
                <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $timeFormatted }}</p>
            </div>
        </div>

        @if ($totalStudents == 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-8 text-center text-gray-500 dark:text-gray-400">
                No attendance records found for the selected date range.
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-2">By Gender</h3>
                    <div wire:ignore x-data="{
                        chart: null,
                        render(data) {
                            if (this.chart) { this.chart.destroy(); }
                            this.chart = KipepeoCharts.bar(this.$refs.canvas, { labels: data.labels, data: data.data, horizontal: true });
                        },
                    }"
                        x-init="render(@js(['labels' => $studentsByGender->keys(), 'data' => $studentsByGender->values()]))"
                        wire:key="gender-chart-{{ $studentsByGender->sum() }}-{{ $fromDate }}-{{ $toDate }}"
                        class="relative h-40">
                        <canvas x-ref="canvas" role="img" aria-label="Attendance by gender"></canvas>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-2">By School</h3>
                    <div wire:ignore x-data="{
                        chart: null,
                        render(data) {
                            if (this.chart) { this.chart.destroy(); }
                            this.chart = KipepeoCharts.bar(this.$refs.canvas, { labels: data.labels, data: data.data, horizontal: true, seriesIndex: 1 });
                        },
                    }"
                        x-init="render(@js(['labels' => $studentsBySchool->keys(), 'data' => $studentsBySchool->values()]))"
                        wire:key="school-chart-{{ $studentsBySchool->sum() }}-{{ $fromDate }}-{{ $toDate }}"
                        class="relative h-40">
                        <canvas x-ref="canvas" role="img" aria-label="Attendance by school"></canvas>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-2">By Grade</h3>
                    <div wire:ignore x-data="{
                        chart: null,
                        render(data) {
                            if (this.chart) { this.chart.destroy(); }
                            this.chart = KipepeoCharts.bar(this.$refs.canvas, { labels: data.labels, data: data.data, horizontal: true, seriesIndex: 2 });
                        },
                    }"
                        x-init="render(@js(['labels' => $studentsByGrade->keys(), 'data' => $studentsByGrade->values()]))"
                        wire:key="grade-chart-{{ $studentsByGrade->sum() }}-{{ $fromDate }}-{{ $toDate }}"
                        class="relative h-40">
                        <canvas x-ref="canvas" role="img" aria-label="Attendance by grade"></canvas>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-2">By Age</h3>
                    <div wire:ignore x-data="{
                        chart: null,
                        render(data) {
                            if (this.chart) { this.chart.destroy(); }
                            this.chart = KipepeoCharts.bar(this.$refs.canvas, { labels: data.labels, data: data.data, horizontal: true, seriesIndex: 3 });
                        },
                    }"
                        x-init="render(@js(['labels' => $studentsByAge->keys(), 'data' => $studentsByAge->values()]))"
                        wire:key="age-chart-{{ $studentsByAge->sum() }}-{{ $fromDate }}-{{ $toDate }}"
                        class="relative h-40">
                        <canvas x-ref="canvas" role="img" aria-label="Students by age"></canvas>
                    </div>
                </div>
            </div>

            <!-- Daily Attendance Statistics Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 page-break mb-6 no-print">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Daily Breakdown</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Total Students</th>
                                <th class="px-4 py-3">Avg. Duration</th>
                                <th class="px-4 py-3">By Gender</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dailyStatisticsPage as $statistics)
                                <tr class="border-b dark:border-gray-700">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                        {{ \Carbon\Carbon::parse($statistics['date'])->format('M j, Y') }}</td>
                                    <td class="px-4 py-3">{{ $statistics['totalStudents'] }}</td>
                                    <td class="px-4 py-3">{{ $statistics['averageAttendanceDuration'] }}</td>
                                    <td class="px-4 py-3">
                                        @foreach ($statistics['studentsByGender'] as $gender => $count)
                                            <span
                                                class="inline-block mr-2">{{ ucfirst($gender) }}: {{ $count }}</span>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $dailyStatisticsPage->links() }}</div>
            </div>

            <!-- Daily Breakdown: print only, every day in range -->
            <div class="hidden print:block page-break mb-6">
                <h3 class="text-lg font-medium mb-2">Daily Breakdown</h3>
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Total Students</th>
                            <th class="px-4 py-3">Avg. Duration</th>
                            <th class="px-4 py-3">By Gender</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($dailyStatistics as $statistics)
                            <tr class="border-b">
                                <td class="px-4 py-3 font-medium">
                                    {{ \Carbon\Carbon::parse($statistics['date'])->format('M j, Y') }}</td>
                                <td class="px-4 py-3">{{ $statistics['totalStudents'] }}</td>
                                <td class="px-4 py-3">{{ $statistics['averageAttendanceDuration'] }}</td>
                                <td class="px-4 py-3">
                                    @foreach ($statistics['studentsByGender'] as $gender => $count)
                                        <span class="inline-block mr-2">{{ ucfirst($gender) }}: {{ $count }}</span>
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Hours by Student Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden page-break mb-6 no-print">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-white px-4 pt-4">Hours by Student</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">Student</th>
                                <th class="px-4 py-3">Gender</th>
                                <th class="px-4 py-3">Days Present</th>
                                <th class="px-4 py-3">Total Hours</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($hoursByStudentPage as $row)
                                <tr class="border-b dark:border-gray-700">
                                    <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $row['student']?->name ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $row['student']?->gender ? ucfirst(strtolower($row['student']->gender)) : '—' }}</td>
                                    <td class="px-4 py-3">{{ $row['visits'] }}</td>
                                    <td class="px-4 py-3">{{ $this->secondsToHms($row['totalSeconds']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                        No student attendance found for the selected filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3">{{ $hoursByStudentPage->links() }}</div>
            </div>

            <!-- Hours by Student: print only, every student in range -->
            <div class="hidden print:block page-break mb-6">
                <h3 class="text-lg font-medium mb-2">Hours by Student</h3>
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">Student</th>
                            <th class="px-4 py-3">Gender</th>
                            <th class="px-4 py-3">Days Present</th>
                            <th class="px-4 py-3">Total Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($hoursByStudent as $row)
                            <tr class="border-b">
                                <td class="px-4 py-3 font-medium">{{ $row['student']?->name ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $row['student']?->gender ? ucfirst(strtolower($row['student']->gender)) : '—' }}</td>
                                <td class="px-4 py-3">{{ $row['visits'] }}</td>
                                <td class="px-4 py-3">{{ $this->secondsToHms($row['totalSeconds']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center">No student attendance found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Hours by Grade Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden page-break mb-6 no-print">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-white px-4 pt-4">Hours by Grade</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">Grade</th>
                                <th class="px-4 py-3">Students</th>
                                <th class="px-4 py-3">Total Hours</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($hoursByGradePage as $row)
                                <tr class="border-b dark:border-gray-700">
                                    <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $row['grade'] }}</td>
                                    <td class="px-4 py-3">{{ $row['students'] }}</td>
                                    <td class="px-4 py-3">{{ $this->secondsToHms($row['totalSeconds']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                        No graded attendance found for the selected filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3">{{ $hoursByGradePage->links() }}</div>
            </div>

            <!-- Hours by Grade: print only, every grade in range -->
            <div class="hidden print:block page-break mb-6">
                <h3 class="text-lg font-medium mb-2">Hours by Grade</h3>
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">Grade</th>
                            <th class="px-4 py-3">Students</th>
                            <th class="px-4 py-3">Total Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($hoursByGrade as $row)
                            <tr class="border-b">
                                <td class="px-4 py-3 font-medium">{{ $row['grade'] }}</td>
                                <td class="px-4 py-3">{{ $row['students'] }}</td>
                                <td class="px-4 py-3">{{ $this->secondsToHms($row['totalSeconds']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center">No graded attendance found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Girls Attendance & Consistency Table -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden page-break mb-6 no-print">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-white px-4 pt-4">Girls Attendance &amp; Consistency</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 px-4">
                    Consistency is days present divided by weekdays in the selected range. Sorted most to least consistent.
                </p>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">#</th>
                                <th class="px-4 py-3">Student</th>
                                <th class="px-4 py-3">Grade</th>
                                <th class="px-4 py-3">Days Present</th>
                                <th class="px-4 py-3">Total Hours</th>
                                <th class="px-4 py-3">Consistency</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($girlsAttendancePage as $row)
                                <tr class="border-b dark:border-gray-700 {{ $row['rank'] <= 5 ? 'bg-primary-50/50 dark:bg-primary-900/10' : '' }}">
                                    <td class="px-4 py-3">
                                        {{ $row['rank'] }}
                                        @if ($row['rank'] <= 5)
                                            <span class="ml-1 inline-block px-1.5 py-0.5 text-xs rounded bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-300">Top</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $row['student']?->name ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $row['student']?->grades->first()?->gradeTable?->grade ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $row['daysPresent'] }}</td>
                                    <td class="px-4 py-3">{{ $this->secondsToHms($row['totalSeconds']) }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <div class="w-20 h-1.5 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                                <div class="h-full bg-primary-600" style="width: {{ min(100, $row['consistency']) }}%"></div>
                                            </div>
                                            <span>{{ $row['consistency'] }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                        No girls' attendance found for the selected filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3">{{ $girlsAttendancePage->links() }}</div>
            </div>

            <!-- Girls Attendance & Consistency: print only, every girl in range -->
            <div class="hidden print:block page-break mb-6">
                <h3 class="text-lg font-medium mb-2">Girls Attendance &amp; Consistency</h3>
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Student</th>
                            <th class="px-4 py-3">Grade</th>
                            <th class="px-4 py-3">Days Present</th>
                            <th class="px-4 py-3">Total Hours</th>
                            <th class="px-4 py-3">Consistency</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($girlsAttendance as $row)
                            <tr class="border-b">
                                <td class="px-4 py-3">{{ $row['rank'] }}{{ $row['rank'] <= 5 ? ' (Top)' : '' }}</td>
                                <td class="px-4 py-3 font-medium">{{ $row['student']?->name ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $row['student']?->grades->first()?->gradeTable?->grade ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $row['daysPresent'] }}</td>
                                <td class="px-4 py-3">{{ $this->secondsToHms($row['totalSeconds']) }}</td>
                                <td class="px-4 py-3">{{ $row['consistency'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center">No girls' attendance found for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($studentId)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden page-break no-print">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white px-4 pt-4">Attendance Log</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                                <tr>
                                    <th class="px-4 py-3">Date</th>
                                    <th class="px-4 py-3">Time In / Out</th>
                                    <th class="px-4 py-3">Total Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($attendanceLogPage as $attendance)
                                    <tr wire:key="{{ $attendance->id }}" class="border-b dark:border-gray-700">
                                        <td class="px-4 py-3">{{ \Carbon\Carbon::parse($attendance->date)->format('M j, Y') }}</td>
                                        <td class="px-4 py-3">
                                            @forelse ($attendance->attrs as $attr)
                                                <div>{{ \Carbon\Carbon::parse($attr->time_in)->format('H:i') }} &ndash; {{ $attr->time_out ? \Carbon\Carbon::parse($attr->time_out)->format('H:i') : 'Still in' }}</div>
                                            @empty
                                                <span class="text-gray-400 dark:text-gray-500">&mdash;</span>
                                            @endforelse
                                        </td>
                                        <td class="px-4 py-3">{{ $this->secondsToHms($attendance->total_time) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                            No attendance logged for this student in range.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="px-4 py-3">{{ $attendanceLogPage->links() }}</div>
                </div>

                <!-- Attendance Log: print only, every entry in range -->
                <div class="hidden print:block page-break">
                    <h3 class="text-lg font-medium mb-2">Attendance Log</h3>
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase bg-gray-50">
                            <tr>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Time In / Out</th>
                                <th class="px-4 py-3">Total Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($attendanceLog as $attendance)
                                <tr class="border-b">
                                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($attendance->date)->format('M j, Y') }}</td>
                                    <td class="px-4 py-3">
                                        @forelse ($attendance->attrs as $attr)
                                            <div>{{ \Carbon\Carbon::parse($attr->time_in)->format('H:i') }} &ndash; {{ $attr->time_out ? \Carbon\Carbon::parse($attr->time_out)->format('H:i') : 'Still in' }}</div>
                                        @empty
                                            <span>&mdash;</span>
                                        @endforelse
                                    </td>
                                    <td class="px-4 py-3">{{ $this->secondsToHms($attendance->total_time) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center">No attendance logged for this student in range.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        @endif
        </div>

        <div wire:loading wire:target="filter"
            class="no-print absolute inset-0 z-10 flex items-center justify-center rounded-lg bg-white/60 dark:bg-gray-900/60">
            <x-spinner class="h-8 w-8" />
        </div>
    </div>
</div>
