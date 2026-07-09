<div>
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
            <tr>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                    Copy Number
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                    Status
                </th>
                @if (auth()->user()->isAdmin())
                    <th scope="col"
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                        Actions
                    </th>
                @endif
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
            @forelse ($copies as $copy)
                <tr wire:key="{{ $copy->id }}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900 dark:text-white">{{ $copy->copy_number }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if ($copy->status == 'borrowed')
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                Borrowed
                            </span>
                        @elseif($copy->status == 'lost')
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                Lost
                            </span>
                        @elseif($copy->status == 'available')
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                Available
                            </span>
                        @elseif($copy->status == 'stolen')
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                                Stolen
                            </span>
                        @endif
                    </td>
                    @if (auth()->user()->isAdmin())
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center gap-3">
                                @if ($copy->status !== 'available')
                                    <button wire:click="markAsAvailable({{ $copy->id }})"
                                        wire:loading.attr="disabled" wire:loading.class="opacity-50"
                                        wire:target="markAsAvailable({{ $copy->id }})"
                                        class="text-green-600 hover:text-green-800 dark:text-green-400">Mark available</button>
                                @endif
                                @if ($copy->status !== 'lost')
                                    <button wire:click="markAsLost({{ $copy->id }})"
                                        wire:confirm="Mark copy {{ $copy->copy_number }} as lost?"
                                        wire:loading.attr="disabled" wire:loading.class="opacity-50"
                                        wire:target="markAsLost({{ $copy->id }})"
                                        class="text-yellow-600 hover:text-yellow-800 dark:text-yellow-400">Mark lost</button>
                                @endif
                                @if ($copy->status !== 'stolen')
                                    <button wire:click="markAsStolen({{ $copy->id }})"
                                        wire:confirm="Mark copy {{ $copy->copy_number }} as stolen?"
                                        wire:loading.attr="disabled" wire:loading.class="opacity-50"
                                        wire:target="markAsStolen({{ $copy->id }})"
                                        class="text-red-600 hover:text-red-800 dark:text-red-400">Mark stolen</button>
                                @endif
                            </div>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap" colspan="3">
                        <div class="text-sm text-gray-500 dark:text-gray-300">No copies found</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {{ $copies->links() }}
</div>
