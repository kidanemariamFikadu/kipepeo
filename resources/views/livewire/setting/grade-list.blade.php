<div>
    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700 p-4 mr-4">
        <!-- Modal header -->
        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Grades
            </h3>
        </div>
        <!-- Modal body -->
        <div>
            <button wire:click="$dispatch('openModal', { component: 'setting.grade' })"
                class="px-3 py-1 bg-teal-500 text-white rounded mb-4 mt-2">+ Add Grade</button>
        </div>
        <ul class="divide-y divide-gray-200 dark:divide-gray-700 mt-2">
            <div class="flex flex-wrap">
                @php
                    $totalGrades= $this->gradeList->count();
                    $chunkSize = floor($totalGrades / 2) + ($totalGrades % 2);
                @endphp
                @foreach ($this->gradeList->chunk($chunkSize) as $chunk)
                    <div class="pr-2 w-1/2">
                        @foreach ($chunk as $grade)
                            <li class="pb-3 sm:pb-4">
                                <div class="flex items-center space-x-4 rtl:space-x-reverse">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $grade->grade }}
                                        </p>
                                    </div>
                                    <div
                                        class="inline-flex items-center text-base font-semibold text-gray-900 dark:text-white">
                                        <div class="inline-flex rounded-md shadow-sm" role="group">
                                            <button title="edit grade"
                                                class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white"
                                                wire:click="$dispatch('openModal', { component: 'setting.grade', arguments: { gradeId: {{ $grade->id }} }})">
                                                <svg class="h-5 w-5 text-teal-500" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" />
                                                    <path
                                                        d="M9 7 h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />
                                                    <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />
                                                    <line x1="16" y1="5" x2="19" y2="8" />
                                                </svg>
                                            </button>
                                            <button title="remove grade"
                                                wire:confirm="You are about to delete this grade. Are you sure?"
                                                class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white"
                                                wire:click="removeGrade({{ $grade->id }})">
                                                <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>

                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </ul>
        {{-- {{ $this->gradeList->links() }} --}}
    </div>
</div>
