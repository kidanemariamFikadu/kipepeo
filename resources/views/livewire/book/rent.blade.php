<div>
    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
        <!-- Modal header -->
        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Rent Book ({{ $book?->title }})
            </h3>
            <button type="button" wire:click="closeModal"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                data-modal-toggle="crud-modal">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
        </div>
        <!-- Modal body -->
        <form class="p-4 md:p-5" wire:submit="rent">
            {{ $rental }}
            <div class="grid gap-4 mb-4 grid-cols-2">
                <div class="col-span-2">
                    <label for="student" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Student
                        <span class="text-red-500">*</span></label>
                    <select id="student" wire:model='student'
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        <option selected>Choose a student</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}">
                                {{ $student->name . ' - ' . $student->current_school->name . ' - ' . $student->current_grade->grade }}
                            </option>
                        @endforeach
                    </select>
                    @error('student')
                        <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-span-2">
                    <label for="dueDate" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Due Date
                        <span class="text-red-500">*</span></label>
                    <input
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                        type="date" placeholder="Due Date" wire:model='dueDate' name="dueDate" id="dueDate">
                    @error('dueDate')
                        <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <button type="submit" wire:loading.attr="rent"
                class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                <svg class="h-5 w-5 text-white mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                    <polyline points="17 21 17 13 7 13 7 21" />
                    <polyline points="7 3 7 8 15 8" />
                </svg>
                Borrow
            </button>
        </form>
    </div>
</div>
