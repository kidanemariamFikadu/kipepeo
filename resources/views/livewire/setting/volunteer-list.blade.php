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
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-white">Volunteers</h2>
            <a href="{{ route('settings') }}" class="text-sm text-primary-700 dark:text-primary-300 hover:underline">
                &larr; Back to settings
            </a>
        </div>

    <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 p-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Volunteers
            </h3>
            <button wire:click="$dispatch('openModal', { component: 'setting.volunteer' })"
                class="inline-flex items-center bg-primary-700 hover:bg-primary-800 text-white rounded-lg text-sm px-4 py-2">+ Add Volunteer</button>
        </div>

        <div class="p-4">
            <div class="relative w-full max-w-xs">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="currentColor"
                        viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <input wire:model.live.debounce.300ms="search" type="text"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2"
                    placeholder="Search volunteers">
            </div>
        </div>

        @if ($this->volunteerList->isEmpty())
            <p class="px-4 pb-4 text-sm text-gray-500 dark:text-gray-400">No volunteers found.</p>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4">
                @foreach ($this->volunteerList as $volunteer)
                    <div class="flex items-center justify-between gap-3 py-2 px-4 border-t dark:border-gray-700">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $volunteer->name }}</span>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold
                                    {{ $volunteer->isActive()
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200'
                                        : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                                    {{ $volunteer->isActive() ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $volunteer->phone ?? $volunteer->email ?? '—' }}</p>
                        </div>
                        <div class="flex items-center gap-1 shrink-0">
                            <a href="{{ route('volunteer-detail', $volunteer->id) }}" title="View details"
                                class="p-2 text-teal-600 hover:bg-teal-50 rounded-lg dark:text-teal-300 dark:hover:bg-gray-700">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </a>
                            <button title="Edit volunteer"
                                class="p-2 text-teal-600 hover:bg-teal-50 rounded-lg dark:text-teal-300 dark:hover:bg-gray-700"
                                wire:click="$dispatch('openModal', { component: 'setting.volunteer', arguments: { volunteerId: {{ $volunteer->id }} }})">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" stroke-width="2"
                                    stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" />
                                    <path d="M9 7 h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />
                                    <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />
                                    <line x1="16" y1="5" x2="19" y2="8" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="px-4 pt-2">
            {{ $this->volunteerList->links() }}
        </div>
    </div>
    </div>
</div>
