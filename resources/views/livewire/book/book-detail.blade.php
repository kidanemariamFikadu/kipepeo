<div>
    <button onclick="goBack()">Back</button>
    <div class="flex flex-col">
        <div class="overflow-x-auto">
            <div class="py-2 align middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                    <form class="p-4 md:p-5" wire:submit="create">
                        <div class="grid gap-4 mb-4 grid-cols-2">
                            <div class="col-span-2">
                                <label for="tile"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Title
                                    <span class="text-red-500">*</span></label>
                                <input type="text" wire:model='title' id="title"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                    placeholder="Jane Deo">
                                @error('title')
                                    <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <label for="author"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Author
                                    <span class="text-red-500">*</span></label>
                                <input type="text" wire:model='author' id="author"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                    placeholder="Jane Deo">
                                @error('author')
                                    <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <label for="publisher"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Publisher</label>
                                <input type="text" wire:model='publisher' id="publisher"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                    placeholder="Jane Deo">
                                @error('publisher')
                                    <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <label for="category"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Category</label>
                                <select id="category" wire:model='category'
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                    <option selected>Choose a category</option>
                                    <option value="Story book">Story book</option>
                                    <option value="Supplementary book">Supplementary book</option>
                                    <option value="Grade book">Grade book</option>
                                </select>
                                @error('category')
                                    <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        @if (!$bookId)
                            <button type="submit" wire:loading.attr="disabled"
                                class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                    <polyline points="17 21 17 13 7 13 7 21" />
                                    <polyline points="7 3 7 8 15 8" />
                                </svg>
                                Save
                            </button>
                        @else
                            <button type="submit" wire:loading.attr="disabled"
                                class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                                <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                    <polyline points="17 21 17 13 7 13 7 21" />
                                    <polyline points="7 3 7 8 15 8" />
                                </svg>
                                Update
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>
        <div class="py-2 align middle inline-block min-w-full sm:px-6 lg:px-8">
            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                @livewire('book.copies', ['bookId' => $bookId])
                {{-- {{$bookId}} --}}
            </div>
        </div>
    </div>
</div>

<script>
    function goBack() {
        window.history.back();
    }
</script>
