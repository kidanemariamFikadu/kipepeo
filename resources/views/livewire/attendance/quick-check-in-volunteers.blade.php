<div>
    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Check in/out volunteers</h3>
            <button type="button" wire:click="closeModal"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
        </div>

        <div class="p-4 md:p-5">
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
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                    placeholder="Search volunteers" autofocus>
            </div>

            <ul class="mt-3 divide-y divide-gray-200 dark:divide-gray-600">
                @forelse ($this->results as $volunteer)
                    @php
                        $todayAttendance = $volunteer->attendances->first();
                        $isIn = (bool) $todayAttendance?->current_in;
                    @endphp
                    <li wire:key="{{ $volunteer->id }}" class="flex items-center justify-between gap-3 py-2.5">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700 dark:bg-primary-900 dark:text-primary-200">
                                {{ Str::of($volunteer->name)->explode(' ')->map(fn ($p) => Str::substr($p, 0, 1))->take(2)->implode('') }}
                                @if ($isIn)
                                    <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full bg-green-500 ring-2 ring-white dark:ring-gray-700"></span>
                                @endif
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $volunteer->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                    {{ $volunteer->phone ?? '—' }} @if ($volunteer->email) &middot; {{ $volunteer->email }} @endif
                                </p>
                            </div>
                        </div>
                        @if ($isIn)
                            <button type="button" wire:loading.attr="disabled" wire:target="checkOut({{ $volunteer->id }})"
                                wire:click="checkOut({{ $volunteer->id }})"
                                class="shrink-0 text-xs font-semibold text-red-600 hover:bg-red-50 rounded-lg px-3 py-1.5 dark:text-red-300 dark:hover:bg-gray-600">
                                Check out
                            </button>
                        @else
                            <button type="button" wire:loading.attr="disabled" wire:target="checkIn({{ $volunteer->id }})"
                                wire:click="checkIn({{ $volunteer->id }})"
                                class="shrink-0 text-xs font-semibold text-teal-600 hover:bg-teal-50 rounded-lg px-3 py-1.5 dark:text-teal-300 dark:hover:bg-gray-600">
                                Check in
                            </button>
                        @endif
                    </li>
                @empty
                    <li class="py-4 text-sm text-gray-500 dark:text-gray-400">No volunteers found.</li>
                @endforelse
            </ul>

            @if ($this->results->count() >= 8)
                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">Showing first 8 results — refine your search to narrow.</p>
            @endif
        </div>
    </div>
</div>
