<div>
    {{-- {{$this->schoolReport}} --}}

    <div class="grid grid-cols-2 gap-4">
        <div class="col-span-1">
            {{-- Table --}}
            <div class="relative shadow-md sm:rounded-lg overflow-hidden">
                <div class="flex items-center justify-between d p-4">
                    <div class="flex">
                        <div class="relative w-full">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400"
                                    fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input wire:model.live.debounce.300ms="search" type="text"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2 "
                                placeholder="Search" required="">
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
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
                            @foreach ($schoolReport as $school)
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
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="py-4 px-3">
                    <div class="flex ">
                        <div class="flex space-x-4 items-center mb-3">
                            <label class="w-32 text-sm font-medium text-gray-900 dark:text-gray-300">Per Page</label>
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

        <div class="col-span-1">
            <div class="col-span-1">
                <!-- Pie Chart -->
                <div class="flex justify-center">
                    <canvas id="pieChart" class="w-2/3"></canvas>
                </div>
    
                @push('scripts')
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const ctx = document.getElementById('pieChart').getContext('2d');
                            const data = {
                                labels: ['Male Students', 'Female Students'],
                                datasets: [{
                                    label: 'Gender Distribution',
                                    data: [
                                        {{ $schoolReport->sum('male_students_count') }},
                                        {{ $schoolReport->sum('female_students_count') }}
                                    ],
                                    backgroundColor: ['#4C51BF', '#ED64A6'],
                                }]
                            };
    
                            const config = {
                                type: 'pie',
                                data: data,
                                options: {
                                    responsive: true,
                                    plugins: {
                                        legend: {
                                            position: 'top',
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: function (tooltipItem) {
                                                    return `${tooltipItem.label}: ${tooltipItem.raw}`;
                                                }
                                            }
                                        }
                                    }
                                }
                            };
    
                            const pieChart = new Chart(ctx, config);
                        });
                    </script>
                @endpush
            </div>
        </div>
    </div>
</div>
