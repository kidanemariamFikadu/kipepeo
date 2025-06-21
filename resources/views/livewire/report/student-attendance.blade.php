<div class="p-6">
    <h2 class="text-2xl font-semibold text-gray-700 dark:text-white mb-4 border-b border-gray-200 dark:border-gray-700">
        Student In Attendance Report
    </h2>

    <!-- Date Filter Form -->
    <form wire:submit.prevent="getStudentByDate" class="mb-4">
        <div class="flex items-center space-x-4">
            <div class="flex-1">
                <label for="date" class="block text-sm font-medium text-gray-700 dark:text-gray-400">Date</label>
                <input type="date" id="date" wire:model="date"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 " />
            </div>
            <div class="flex items-center space-x-2 mt-6">
                <button type="submit" class="p-2 bg-blue-500 text-white rounded">Filter</button>
                <button type="button" onclick="window.print()" class="p-2 bg-green-500 text-white rounded">Print
                    Report</button>
            </div>
        </div>
    </form>

    <div class="printable">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Stundets attendance</h3>
                    <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th>Name</th>
                                <th>Guardian</th>
                                <th>Time stayed</th>
                                <th>Attributes</th>
                            </tr>
                        </thead>
                        <tbody class="dark:text-white">
                            @if ($students == null || $students->isEmpty())
                                <tr>
                                    <td colspan="3" class="text-center text-gray-500">No students found for the
                                        selected date.</td>
                                </tr>
                            @else
                                @foreach ($students as $student)
                                    <tr class="border-b dark:border-gray-700">
                                        <td>{{ $student['name'] }}</td>
                                        <td>{{ $student['guardians']?->pluck('guardian_name')->implode(', ') }}</td>
                                        <td>{{ $student['total_time'] }}</td>
                                        <td>
                                            @if (isset($student['attributes']) && $student['attributes'] instanceof \Illuminate\Support\Collection)
                                                {{ $student['attributes']->map(function ($attr) {
                                                        return $attr['time_in'] . '-' . ($attr['time_out'] ?: 'N/A');
                                                    })->implode(', ') }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
