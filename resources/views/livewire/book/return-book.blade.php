<div>
    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
        <!-- Modal header -->
        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Return Book
            </h3>
            <x-modal-close type="button" wire:click="closeModal"
                data-modal-toggle="crud-modal" />
        </div>
        <!-- Modal body -->
        <form class="p-4 md:p-5" wire:submit="returnBook">
            <div class="grid gap-4 mb-4 grid-cols-2">
                <div class="col-span-2 sm:col-span-1">
                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Book Title</label>
                    <span class="block mb-2 text-sm text-gray-900 dark:text-white">{{ $rental->book?->title ?? '(book removed)' }}</span>
                </div>

                <div class="col-span-2 sm:col-span-1">
                    <label class="block mt-4 mb-2 text-sm font-medium text-gray-900 dark:text-white">Rented To</label>
                    <span
                        class="block mb-2 text-sm text-gray-900 dark:text-white">{{ $rental->checkedOutTo?->name ?? '(student removed)' }}</span>
                </div>

                <div class="col-span-2 sm:col-span-1">
                    <label class="block mt-4 mb-2 text-sm font-medium text-gray-900 dark:text-white">Rented At</label>
                    <span class="block mb-2 text-sm text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($rental->rented_at)->format('Y-m-d') }}</span>
                </div>

                <div class="col-span-2 sm:col-span-1">
                    <label class="block mt-4 mb-2 text-sm font-medium text-gray-900 dark:text-white">Due Date</label>
                    <span class="block mb-2 text-sm text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($rental->due_at)->format('Y-m-d') }}</span>
                </div>

                <div class="col-span-2">
                    <label for="comment"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Comment</label>
                    <textArea
                        class="
                        bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                        wire:model='comment' id="comment" placeholder="Book condition"></textArea>
                    @error('comment')
                        <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <x-button type="submit" wire:loading.attr="disabled" wire:target="returnBook">
                <x-spinner class="h-4 w-4 mr-2 text-white" wire:loading wire:target="returnBook" />
                Return
            </x-button>
        </form>
    </div>
</div>
