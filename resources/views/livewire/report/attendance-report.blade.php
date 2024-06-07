<div class="p-6">
    <h2 class="text-2xl font-semibold text-gray-700 dark:text-white mb-4 border-b border-gray-200 dark:border-gray-700">
        Student Attendance Report
    </h2>

    <!-- Date Filter Form -->
    <form wire:submit.prevent="filter" class="mb-4">
        <div class="flex items-center space-x-4">
            <div class="flex-1">
                <label for="fromDate" class="block text-sm font-medium text-gray-700 dark:text-gray-400">From Date</label>
                <input type="date" id="fromDate" wire:model="fromDate" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 " />
            </div>
            <div class="flex-1">
                <label for="toDate" class="block text-sm font-medium text-gray-700 dark:text-gray-400">To Date</label>
                <input type="date" id="toDate" wire:model="toDate" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 " />
            </div>
            <div class="flex items-center space-x-2 mt-6">
                <button type="submit" class="p-2 bg-blue-500 text-white rounded">Filter</button>
                <button type="button" onclick="window.print()" class="p-2 bg-green-500 text-white rounded">Print Report</button>
            </div>
        </div>
    </form>

    <div class="printable">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="col-span-1">
                <!-- Summary Report Table -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Summary Report</h3>
                    <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th>Metric</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody class="dark:text-white">
                            <tr>
                                <td>Total Students</td>
                                <td>{{ $totalStudents }}</td>
                            </tr>
                            <tr>
                                <td>Average Attendance Duration</td>
                                <td>{{ $timeFormatted }} hours</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Daily Attendance Statistics Table -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Daily Attendance Statistics</h3>
                    <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th>Date</th>
                                <th>Total Students</th>
                                <th>Average Attendance Duration</th>
                                <th>Students by Gender</th>
                            </tr>
                        </thead>
                        <tbody class="dark:text-white">
                            @foreach ($dailyStatistics as $date => $statistics)
                                <tr class="border-b dark:border-gray-700">
                                    <td>{{ $date }}</td>
                                    <td>{{ $statistics['totalStudents'] }}</td>
                                    <td>{{ $statistics['averageAttendanceDuration'] }}</td>
                                    <td>
                                        @foreach ($statistics['studentsByGender'] as $gender => $count)
                                            {{ ucfirst($gender) }}: {{ $count }}<br>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="col-span-1 page-break">
                <!-- Additional Report Data -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Breakdown</h3>
                    <ul class="dark:text-white">
                        <li class="mb-2">
                            <strong>By Gender:</strong>
                            <ul class="ml-4">
                                @foreach ($studentsByGender as $gender => $count)
                                    <li>{{ ucfirst($gender) }}: {{ $count }}</li>
                                @endforeach
                            </ul>
                        </li>
                        <li class="mb-2">
                            <strong>By School:</strong>
                            <ul class="ml-4">
                                @foreach ($studentsBySchool as $school => $count)
                                    <li>{{ $school }}: {{ $count }}</li>
                                @endforeach
                            </ul>
                        </li>
                        <li class="mb-2">
                            <strong>By Grade:</strong>
                            <ul class="ml-4">
                                @foreach ($studentsByGrade as $grade => $count)
                                    <li>{{ $grade }}: {{ $count }}</li>
                                @endforeach
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
