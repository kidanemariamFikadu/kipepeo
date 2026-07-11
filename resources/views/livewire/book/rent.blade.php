<div>
    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
        <!-- Modal header -->
        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $bookId ? 'Rent Book (' . $book?->title . ')' : 'Rent a Book' }}</h3>
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

        @unless ($bookId)
            <!-- Book search step -->
            <div class="p-4 md:p-5">
                <label for="bookSearch" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Find a book</label>
                <input wire:model.live.debounce.300ms="bookSearch" type="text" id="bookSearch"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                    placeholder="Search by title or author" autofocus>

                <ul class="mt-3 divide-y divide-gray-200 dark:divide-gray-600">
                    @forelse ($this->searchableBooks as $searchableBook)
                        <li wire:key="{{ $searchableBook->id }}" class="flex items-center justify-between gap-3 py-2.5">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-200">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M4 19.5A2.5 2.5 0 016.5 17H20" />
                                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z" />
                                    </svg>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $searchableBook->title }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $searchableBook->author }} &middot; {{ $searchableBook->available_copies }} available</p>
                                </div>
                            </div>
                            <button type="button" wire:click="selectBook({{ $searchableBook->id }})"
                                {{ $searchableBook->available_copies < 1 ? 'disabled' : '' }}
                                class="shrink-0 text-xs font-semibold text-white bg-primary-700 hover:bg-primary-800 rounded-lg px-3 py-1.5 disabled:opacity-50 dark:bg-primary-600 dark:hover:bg-primary-700">
                                Select
                            </button>
                        </li>
                    @empty
                        <li class="py-4 text-sm text-gray-500 dark:text-gray-400">No books found.</li>
                    @endforelse
                </ul>
            </div>
        @else
        <!-- Modal body -->
        <form class="p-4 md:p-5" wire:submit="rent">
            <button type="button" wire:click="changeBook"
                class="mb-3 text-xs text-primary-700 dark:text-primary-300 hover:underline">
                &larr; Change book
            </button>
            <div class="grid gap-4 mb-4 grid-cols-2">
                <div class="col-span-2">
                    <label for="student" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Student
                        <span class="text-red-500">*</span></label>
                    <select id="student" wire:model='student'
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        <option value="" selected>Choose a student</option>
                        @foreach ($students as $student)
                            <option value="{{ $student->id }}">
                                {{ $student->name }}{{ $student->graduated_at ? ' - Alumni' . ($student->graduatedGrade ? ' (' . $student->graduatedGrade->grade . ')' : '') : ($student->current_school ? ' - ' . $student->current_school->name : '') . ($student->current_grade ? ' - ' . $student->current_grade->grade : '') }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Students marked present today, plus alumni, are listed.</p>
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
            <button type="submit" wire:loading.attr="disabled" wire:target="rent"
                class="text-white inline-flex items-center bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 disabled:opacity-50">
                <svg class="h-5 w-5 text-white mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    wire:loading.remove wire:target="rent">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                    <polyline points="17 21 17 13 7 13 7 21" />
                    <polyline points="7 3 7 8 15 8" />
                </svg>
                <x-spinner class="h-5 w-5 text-white mr-2" wire:loading wire:target="rent" />
                Borrow
            </button>
        </form>
        @endunless
    </div>
</div>
