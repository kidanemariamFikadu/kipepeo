<div>
    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700 p-4 mr-4">
        <!-- Modal header -->
        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Schools
            </h3>
        </div>
        <!-- Modal body -->
        <div class="flex items-center justify-between d p-4">

            <div>
                <button wire:click="$dispatch('openModal', { component: 'setting.school' })"
                    class="px-3 py-1 bg-teal-500 text-white rounded mb-4 mt-2">+ Add School</button>
            </div>
            <div class="flex">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor"
                            viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
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
        <ul class=" divide-y divide-gray-200 dark:divide-gray-700 mt-2">
            <div class="flex flex-wrap">
                @foreach ($this->schoolList->chunk(10) as $chunk)
                    <div class="pr-2 w-1/2">
                        @foreach ($chunk as $school)
                            <li class="pb-3 sm:pb-4">
                                <div class="flex items-center space-x-4 rtl:space-x-reverse">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $school->name }}
                                        </p>
                                    </div>
                                    <div
                                        class="inline-flex items-center text-base font-semibold text-gray-900 dark:text-white">
                                        <div class="inline-flex rounded-md shadow-sm" role="group">
                                            <button title="edit school"
                                                class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white"
                                                wire:click="$dispatch('openModal', { component: 'setting.school', arguments: { schoolId: {{ $school->id }} }})">
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
                                            <button title="remove school"
                                                wire:confirm="You are about to delete this school. Are you sure?"
                                                class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white"
                                                wire:click="removeSchool({{ $school->id }})">
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
                <div class="flex flex-wrap">
        </ul>
        {{ $this->schoolList->links() }}
    </div>
</div>


{{-- <div>
    <div class="flex flex-wrap">
        @foreach ($this->schoolList->chunk(10) as $chunk)
            <div class="w-1/2">
                @foreach ($chunk as $school)
                    <div class="mb-4 p-2 border rounded">
                        <span>{{ $school->name }}</span>
                        <button wire:click="editSchool({{ $school->id }})">Edit</button>
                        <button wire:click="deleteSchool({{ $school->id }})">Delete</button>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div> --}}
