<div>
    <h2 class="text-gray-700 dark:text-white mb-4 border-b border-gray-200 dark:border-gray-700">
        Student Attendance Report
    </h2>

    <!-- Date Filter Form -->
    <form wire:submit.prevent="filter" class="mb-4">
        <div class="flex space-x-4">
            <div class="flex-1">
                <label for="fromDate" class="block text-sm font-medium text-gray-700">From Date</label>
                <input type="date" id="fromDate" wire:model="fromDate" class="mt-1 block w-full border rounded p-2" />
            </div>
            <div class="flex-1">
                <label for="toDate" class="block text-sm font-medium text-gray-700">To Date</label>
                <input type="date" id="toDate" wire:model="toDate" class="mt-1 block w-full border rounded p-2" />
            </div>
        </div>
        <button type="submit" class="mt-4 p-2 bg-blue-500 text-white rounded">Filter</button>
    </form>

    <div class="grid grid-cols-2 gap-4">
        <div class="col-span-1">
            <!-- Report Table -->
            <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <tr>
                        <th>Metric</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody class=" dark:text-white">
                    <tr>
                        <td>Total Students</td>
                        <td>{{ $totalStudents }}</td>
                    </tr>
                    <tr>
                        <td>Average Attendance Duration</td>
                        <td>{{ $timeFormatted }} hours</td>
                    </tr>
                    <tr>
                        <td>Average Age</td>
                        <td>{{ print_r($studentsByAge) }}</td>
                    </tr>
                </tbody>
            </table>

            <h1 class="mt-5  dark:text-white">Daily Attendance Statistics</h1>
            <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                    <th>Date</th>
                    <th>Total Students</th>
                    <th>Average Attendance Duration</th>
                    <th>Students by Gender</th>
                </thead>
                <tbody>
                    @foreach ($dailyStatistics as $date => $statistics)
                        <tr class=" dark:text-white">
                            <td>{{ $date }}</td>
                            <td>{{ $statistics['totalStudents'] }}</td>
                            <td>{{ $statistics['averageAttendanceDuration'] }}</td>
                            <td>
                                @foreach ($statistics['studentsByGender'] as $gender => $count)
                                    {{ $gender }}: {{ $count }}<br>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="col-span-1">
            <!-- Additional Report Data -->
            <h3 class="text-lg font-medium text-gray-900  dark:text-white">Breakdown</h3>
            <ul class=" dark:text-white">
                <li>
                    <strong>By Gender:</strong>
                    <ul>
                        @foreach ($studentsByGender as $gender => $count)
                            <li>{{ ucfirst($gender) }}: {{ $count }}</li>
                        @endforeach
                    </ul>
                </li>
                <li>
                    <strong>By School:</strong>
                    <ul>
                        @foreach ($studentsBySchool as $school => $count)
                            <li>{{ $school }}: {{ $count }}</li>
                        @endforeach
                    </ul>
                </li>
                <li>
                    <strong>By Grade:</strong>
                    <ul>
                        @foreach ($studentsByGrade as $grade => $count)
                            <li>{{ $grade }}: {{ $count }}</li>
                        @endforeach
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>
