<div>
    <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 p-4">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Activity Types
            </h3>
            <button wire:click="$dispatch('openModal', { component: 'setting.activity-type' })"
                class="inline-flex items-center bg-primary-700 hover:bg-primary-800 text-white rounded-lg text-sm px-4 py-2">+ Add Activity Type</button>
        </div>
        <ul class="divide-y divide-gray-200 dark:divide-gray-700 mt-2">
            <div class="flex flex-wrap">
                @php
                    $totalActivityTypes = $this->activityTypeList->count();
                    $chunkSize = floor($totalActivityTypes / 2) + ($totalActivityTypes % 2);
                @endphp
                @if ($totalActivityTypes === 0)
                    <p class="px-2 pb-2 text-sm text-gray-500 dark:text-gray-400">No activity types yet.</p>
                @endif
                @foreach ($this->activityTypeList->chunk(max($chunkSize, 1)) as $chunk)
                    <div class="pr-2 w-1/2">
                        @foreach ($chunk as $activityType)
                            <li class="pb-3 sm:pb-4">
                                <div class="flex items-center space-x-4 rtl:space-x-reverse">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $activityType->name }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $activityType->category ? ucfirst($activityType->category->value) : 'No category' }}
                                        </p>
                                    </div>
                                    <div
                                        class="inline-flex items-center text-base font-semibold text-gray-900 dark:text-white">
                                        <div class="inline-flex rounded-md shadow-sm" role="group">
                                            <button title="edit activity type"
                                                class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white"
                                                wire:click="$dispatch('openModal', { component: 'setting.activity-type', arguments: { activityTypeId: {{ $activityType->id }} }})">
                                                <svg class="h-5 w-5 text-teal-500" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" />
                                                    <path
                                                        d="M9 7 h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />
                                                    <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />
                                                    <line x1="16" y1="5" x2="19" y2="8" />
                                                </svg>
                                            </button>
                                            <button title="remove activity type"
                                                wire:confirm="You are about to delete this activity type. Are you sure?"
                                                wire:loading.attr="disabled" wire:target="removeActivityType({{ $activityType->id }})"
                                                class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white"
                                                wire:click="removeActivityType({{ $activityType->id }})">
                                                <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor"
                                                    wire:loading.remove wire:target="removeActivityType({{ $activityType->id }})">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <x-spinner class="h-5 w-5" wire:loading wire:target="removeActivityType({{ $activityType->id }})" />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </ul>
    </div>
</div>
