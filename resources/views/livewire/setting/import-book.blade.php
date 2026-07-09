<div>
    <form class="p-4 md:p-5" wire:submit="importBooks">
        <div class="grid gap-4 mb-4 grid-cols-1">
            <div class="col-span-1 sm:col-span-1">
                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="file_input">Upload
                    file</label>
                <input
                    class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400"
                    aria-describedby="file_input_help" id="file_input" wire:model="books" type="file">
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-300" id="file_input_help"> Allowed file formats are
                    xlsx, xls, csv
                </p>
                @error('books')
                    <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                @enderror
            </div>
        </div>
        <button type="submit" wire:loading.attr="disabled" wire:target="importBooks"
            class="text-white inline-flex items-center bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 disabled:opacity-50">
            <svg class="h-5 w-5 text-white mr-2" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                wire:loading.remove wire:target="importBooks">
                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                <polyline points="17 21 17 13 7 13 7 21" />
                <polyline points="7 3 7 8 15 8" />
            </svg>
            <x-spinner class="h-5 w-5 text-white mr-2" wire:loading wire:target="importBooks" />
            <span wire:loading.remove wire:target="importBooks">Submit</span>
            <span wire:loading wire:target="importBooks">Uploading…</span>
        </button>
    </form>
</div>
