<div>
    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
        <!-- Modal header -->
        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Return Book
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
        <form class="p-4 md:p-5" wire:submit="returnBook">
            <div class="grid gap-4 mb-4 grid-cols-2">
                <div class="col-span-2 sm:col-span-1">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Book Title</label>
                    <span class="block mb-2 text-sm text-gray-900 dark:text-white">{{ $rental->book->title }}</span>
                </div>

                <div class="col-span-2 sm:col-span-1">
                    <label class="block mt-4 mb-2 text-sm font-medium text-gray-900 dark:text-white">Rented To</label>
                    <span
                        class="block mb-2 text-sm text-gray-900 dark:text-white">{{ $rental->checkedOutTo->name }}</span>
                </div>

                <div class="col-span-2 sm:col-span-1">
                    <label class="block mt-4 mb-2 text-sm font-medium text-gray-900 dark:text-white">Rented At</label>
                    <span class="block mb-2 text-sm text-gray-900 dark:text-white">{{ $rental->rented_at }}</span>
                </div>

                <div class="col-span-2 sm:col-span-1">
                    <label class="block mt-4 mb-2 text-sm font-medium text-gray-900 dark:text-white">Due Date</label>
                    <span class="block mb-2 text-sm text-gray-900 dark:text-white">{{ $rental->due_at }}</span>
                </div>

                <div class="col-span-2">
                    <label for="tile"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Comment</label>
                    <textArea
                        class="
                        bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                        wire:model='title' id="title" placeholder="Book condition"></textArea>
                    @error('title')
                        <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <button type="submit" wire:loading.attr="disabled"
                class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                Return
            </button>
        </form>
    </div>
</div>
