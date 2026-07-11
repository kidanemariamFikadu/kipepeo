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

    <div class="p-2 md:p-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-white mb-4">Volunteers</h2>

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
                            placeholder="Search volunteers">
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-900 dark:text-gray-300">Status</label>
                        <select wire:model.live="currentlyIn"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2.5">
                            <option value="">All</option>
                            <option value="1">In</option>
                            <option value="0">Out</option>
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                            <tr>
                                @include('livewire.includes.table-sortable-th', [
                                    'name' => 'name',
                                    'displayName' => 'Name',
                                ])
                                <th scope="col" class="px-4 py-3">Phone</th>
                                <th scope="col" class="px-4 py-3">Email</th>
                                <th scope="col" class="px-4 py-3">Status</th>
                                <th scope="col" class="px-4 py-3">Total stay</th>
                                <th scope="col" class="px-4 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($volunteers as $volunteer)
                                @php
                                    $todayAttendance = $volunteer->attendances->first();
                                    $isIn = (bool) $todayAttendance?->current_in;
                                @endphp
                                <tr wire:key="{{ $volunteer->id }}" class="border-b dark:border-gray-700">
                                    <th scope="row"
                                        class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        <div class="flex items-center gap-3">
                                            <span class="relative flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700 dark:bg-primary-900 dark:text-primary-200">
                                                {{ Str::of($volunteer->name)->explode(' ')->map(fn ($p) => Str::substr($p, 0, 1))->take(2)->implode('') }}
                                                @if ($isIn)
                                                    <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full bg-green-500 ring-2 ring-white dark:ring-gray-800"></span>
                                                @endif
                                            </span>
                                            {{ $volunteer->name }}
                                        </div>
                                    </th>
                                    <td class="px-4 py-3">{{ $volunteer->phone ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $volunteer->email ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                            {{ $isIn
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200'
                                                : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                                            {{ $isIn ? 'In' : 'Out' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">{{ $volunteer->secondsToHms($todayAttendance?->total_time ?? 0) }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('volunteer-detail', $volunteer->id) }}" title="View details"
                                                class="p-2 text-teal-600 hover:bg-teal-50 rounded-lg dark:text-teal-300 dark:hover:bg-gray-700">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                            </a>
                                            @if ($isIn)
                                                <button title="Log activity"
                                                    class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg dark:text-primary-300 dark:hover:bg-gray-700"
                                                    wire:click="$dispatch('openModal', { component: 'attendance.log-volunteer-activity', arguments: { volunteer: {{ $volunteer->id }} }})">
                                                    <svg class="h-5 w-5" width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                                        stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                        <path stroke="none" d="M0 0h24v24H0z" />
                                                        <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                                        <path d="M17 21H7a2 2 0 0 1 -2 -2V5a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                                        <line x1="9" y1="9" x2="10" y2="9" />
                                                        <line x1="9" y1="13" x2="15" y2="13" />
                                                        <line x1="9" y1="17" x2="15" y2="17" />
                                                    </svg>
                                                </button>
                                                <button title="Check out"
                                                    wire:loading.attr="disabled" wire:target="checkOut({{ $volunteer->id }})"
                                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg dark:text-red-300 dark:hover:bg-gray-700"
                                                    wire:click="checkOut({{ $volunteer->id }})">
                                                    <svg class="h-5 w-5" width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                                        stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                                        wire:loading.remove wire:target="checkOut({{ $volunteer->id }})">
                                                        <path stroke="none" d="M0 0h24v24H0z" />
                                                        <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" />
                                                        <path d="M7 12h14l-3 -3m0 6l3 -3" />
                                                    </svg>
                                                    <x-spinner class="h-5 w-5" wire:loading wire:target="checkOut({{ $volunteer->id }})" />
                                                </button>
                                            @else
                                                <button title="Check in"
                                                    wire:loading.attr="disabled" wire:target="checkIn({{ $volunteer->id }})"
                                                    class="p-2 text-teal-600 hover:bg-teal-50 rounded-lg dark:text-teal-300 dark:hover:bg-gray-700"
                                                    wire:click="checkIn({{ $volunteer->id }})">
                                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                        wire:loading.remove wire:target="checkIn({{ $volunteer->id }})">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                                    </svg>
                                                    <x-spinner class="h-5 w-5" wire:loading wire:target="checkIn({{ $volunteer->id }})" />
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                        No volunteers found.
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
                    {{ $volunteers->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
