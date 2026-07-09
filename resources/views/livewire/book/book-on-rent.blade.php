<div>
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-3 rounded relative" role="alert">
            <strong class="font-bold">Error</strong>
            <span class="block sm:inline">{{ session('error') }}</span>

        </div>
    @elseif (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 mb-3 rounded relative" role="alert">
            <strong class="font-bold">Success</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div>
        <div>
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-white mb-4">Books on Loan</h2>
            <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 p-4">
                    <div class="relative w-full max-w-xs">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400"
                                fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2"
                            placeholder="Search title, author, or student">
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-900 dark:text-gray-300">Status</label>
                        <select wire:model.live="status"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2.5">
                            <option value="">All</option>
                            <option value="returned">Returned</option>
                            <option value="borrowed">Borrowed</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                @include('livewire.includes.table-sortable-th', [
                                    'name' => 'title',
                                    'displayName' => 'Title',
                                ])
                                @include('livewire.includes.table-sortable-th', [
                                    'name' => 'author',
                                    'displayName' => 'Author',
                                ])
                                @include('livewire.includes.table-sortable-th', [
                                    'name' => 'publisher',
                                    'displayName' => 'Publisher',
                                ])
                                @include('livewire.includes.table-sortable-th', [
                                    'name' => 'borrowed_by',
                                    'displayName' => 'Borrowed By',
                                ])
                                @include('livewire.includes.table-sortable-th', [
                                    'name' => 'created_at',
                                    'displayName' => 'Borrowed At',
                                ])
                                @include('livewire.includes.table-sortable-th', [
                                    'name' => 'due_date',
                                    'displayName' => 'Due Date',
                                ])
                                @include('livewire.includes.table-sortable-th', [
                                    'name' => 'returned_at',
                                    'displayName' => 'Returned At',
                                ])
                                <th scope="col" class="px-6 py-3">Status</th>
                                <th scope="col" class="px-4 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($booksOnRent as $bookOnRent)
                                <tr wire:key="{{ $bookOnRent->id }}" class="border-b dark:border-gray-700">
                                    <th scope="row"
                                        class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $bookOnRent->book?->title ?? '(book removed)' }}</th>
                                    <td class="px-4 py-3">{{ $bookOnRent->book?->author ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $bookOnRent->book?->publisher ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $bookOnRent->checkedOutTo?->name ?? '(student removed)' }}</td>
                                    @php
                                        $dueDate = Carbon\Carbon::parse($bookOnRent->due_at);
                                        $createdAt = Carbon\Carbon::parse($bookOnRent->created_at);
                                        $returnedAt = $bookOnRent->returned_at
                                            ? Carbon\Carbon::parse($bookOnRent->returned_at)
                                            : null;
                                        $isLate = $bookOnRent->returned_at
                                            ? false
                                            : Carbon\Carbon::parse($bookOnRent->due_at)->isPast();
                                    @endphp
                                    <td class="px-4 py-3">{{ $createdAt->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3">{{ $dueDate?->format('Y-m-d') }}</td>
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
                                        <div class="flex items-center justify-end">
                                            @if (! $returnedAt)
                                                <button title="Return this book"
                                                    class="p-2 text-teal-600 hover:bg-teal-50 rounded-lg dark:text-teal-300 dark:hover:bg-gray-700"
                                                    wire:click="$dispatch('openModal', { component: 'book.return-book', arguments: { rentalId: {{ $bookOnRent->id }} }})">
                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                        No rentals found.
                                    </td>
                                </tr>
                            @endforelse
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
                    {{ $booksOnRent->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
