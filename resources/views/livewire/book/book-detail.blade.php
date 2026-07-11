<div>
    <div class="p-2 md:p-6">
    <div class="mb-3">
        <a href="{{ route('books') }}"
            class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back
        </a>

        <div class="flex flex-wrap items-center gap-4 mt-3 mb-4">
            <div>
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-white">{{ $this->book->title }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">by {{ $this->book->author }}</p>
            </div>
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-200">
                {{ $this->book->category }}
            </span>
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                {{ $this->book->available_copies > 0
                    ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200'
                    : 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200' }}">
                {{ $this->book->available_copies }} / {{ $this->book->copies }} available
            </span>
        </div>
    </div>

    <div class="flex flex-col">
        <div class="overflow-x-auto">
            <div class="py-2 align middle inline-block min-w-full sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                    <form class="p-4 md:p-5" wire:submit="update">
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
                                    <option value="" selected>Choose a category</option>
                                    <option value="Story book">Story book</option>
                                    <option value="Supplementary book">Supplementary book</option>
                                    <option value="Grade book">Grade book</option>
                                </select>
                                @error('category')
                                    <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-span-2 sm:col-span-1">
                                <label for="copies"
                                    class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Number of Copies
                                    <span class="text-red-500">*</span></label>
                                <input type="number" min="1" wire:model='copies' id="copies"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                @error('copies')
                                    <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button type="submit" wire:loading.attr="disabled" wire:target="update"
                                class="text-white inline-flex items-center bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 disabled:opacity-50">
                                <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    wire:loading.remove wire:target="update">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                                    <polyline points="17 21 17 13 7 13 7 21" />
                                    <polyline points="7 3 7 8 15 8" />
                                </svg>
                                <x-spinner class="h-5 w-5 text-white" wire:loading wire:target="update" />
                                Update
                            </button>
                            @if (auth()->user()->isAdmin())
                                <button type="button" title="Delete book" wire:click="deleteBook"
                                    wire:confirm="Delete &quot;{{ $this->book->title }}&quot;? This can't be undone from here."
                                    wire:loading.attr="disabled" wire:target="deleteBook"
                                    class="text-red-600 inline-flex items-center bg-red-50 hover:bg-red-100 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-gray-700 dark:text-red-300 dark:hover:bg-gray-600 dark:focus:ring-red-800 disabled:opacity-50">
                                    <svg class="h-5 w-5 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        wire:loading.remove wire:target="deleteBook">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <x-spinner class="h-5 w-5 mr-1.5" wire:loading wire:target="deleteBook" />
                                    Delete book
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="py-2 align middle inline-block min-w-full sm:px-6 lg:px-8">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 p-4">
                <div class="flex items-center justify-between p-4 md:p-5 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Rental History
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">Student</th>
                                <th class="px-4 py-3">Rented At</th>
                                <th class="px-4 py-3">Due Date</th>
                                <th class="px-4 py-3">Returned At</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($this->book->rentals as $rental)
                                @php
                                    $returnedAt = $rental->returned_at ? \Carbon\Carbon::parse($rental->returned_at) : null;
                                    $isLate = $returnedAt ? false : \Carbon\Carbon::parse($rental->due_at)->isPast();
                                @endphp
                                <tr wire:key="rental-{{ $rental->id }}" class="border-b dark:border-gray-700">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                        {{ $rental->checkedOutTo?->name ?? '(student removed)' }}
                                    </td>
                                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($rental->rented_at)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($rental->due_at)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3">{{ $returnedAt?->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3">
                                        @if ($returnedAt)
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200">
                                                Returned
                                            </span>
                                        @elseif ($isLate)
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200">
                                                Overdue
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-200">
                                                Borrowed
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        @if (! $returnedAt)
                                            <div class="flex items-center justify-end">
                                                <button title="Return this book"
                                                    class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg dark:text-primary-300 dark:hover:bg-gray-700"
                                                    wire:click="$dispatch('openModal', { component: 'book.return-book', arguments: { rentalId: {{ $rental->id }} }})">
                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                                                    </svg>
                                                </button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                        No rentals recorded for this book yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="py-2 align middle inline-block min-w-full sm:px-6 lg:px-8">
            <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg">
                @livewire('book.copies', ['bookId' => $bookId])
            </div>
        </div>
    </div>
    </div>
</div>
