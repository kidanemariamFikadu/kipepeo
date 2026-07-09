<div class="overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-4 py-3">Copy Number</th>
                <th scope="col" class="px-4 py-3">Status</th>
                @if (auth()->user()->isAdmin())
                    <th scope="col" class="px-4 py-3">
                        <span class="sr-only">Actions</span>
                    </th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($copies as $copy)
                <tr wire:key="{{ $copy->id }}" class="border-b dark:border-gray-700">
                    <th scope="row" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                        {{ $copy->copy_number }}
                    </th>
                    <td class="px-4 py-3">
                        @if ($copy->status == 'borrowed')
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-200">
                                Borrowed
                            </span>
                        @elseif ($copy->status == 'lost')
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200">
                                Lost
                            </span>
                        @elseif ($copy->status == 'available')
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200">
                                Available
                            </span>
                        @elseif ($copy->status == 'stolen')
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200">
                                Stolen
                            </span>
                        @endif
                    </td>
                    @if (auth()->user()->isAdmin())
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                @if ($copy->status !== 'available')
                                    <button title="Mark available" wire:click="markAsAvailable({{ $copy->id }})"
                                        wire:loading.attr="disabled" wire:target="markAsAvailable({{ $copy->id }})"
                                        class="p-2 text-green-600 hover:bg-green-50 rounded-lg dark:text-green-300 dark:hover:bg-gray-700 disabled:opacity-50">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                @endif
                                @if ($copy->status !== 'lost')
                                    <button title="Mark lost" wire:click="markAsLost({{ $copy->id }})"
                                        wire:confirm="Mark copy {{ $copy->copy_number }} as lost?"
                                        wire:loading.attr="disabled" wire:target="markAsLost({{ $copy->id }})"
                                        class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg dark:text-amber-300 dark:hover:bg-gray-700 disabled:opacity-50">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                @endif
                                @if ($copy->status !== 'stolen')
                                    <button title="Mark stolen" wire:click="markAsStolen({{ $copy->id }})"
                                        wire:confirm="Mark copy {{ $copy->copy_number }} as stolen?"
                                        wire:loading.attr="disabled" wire:target="markAsStolen({{ $copy->id }})"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg dark:text-red-300 dark:hover:bg-gray-700 disabled:opacity-50">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ auth()->user()->isAdmin() ? 3 : 2 }}" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                        No copies found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="py-4 px-3">
        {{ $copies->links() }}
    </div>
</div>
