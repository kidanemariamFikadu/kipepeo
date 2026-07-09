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
    <h2 class="text-2xl font-semibold text-gray-700 dark:text-white mb-4">Settings</h2>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <div>
            @livewire('setting.school-list')
        </div>
        <div>
            @livewire('setting.grade-list')
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        <div>
            @livewire('setting.volunteer-list')
        </div>
        <div>
            @livewire('setting.activity-type-list')
        </div>
    </div>

    @if (Auth::user()->isAdmin())
        <div class="mb-4">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 p-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Promote Students</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Move students up to their next grade at the end of a term or school year.
                        </p>
                    </div>
                    <a href="{{ route('promote-students') }}"
                        class="text-white inline-flex items-center justify-center bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                        Promote Students
                    </a>
                </div>
            </div>
        </div>
    @endif
    @if (Auth::user()->isAdmin())
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 p-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Job titles
                    </h3>
                    <button wire:click="$dispatch('openModal', { component: 'setting.job-title' })"
                        class="inline-flex items-center bg-primary-700 hover:bg-primary-800 text-white rounded-lg text-sm px-4 py-2">+ Add Job title</button>
                </div>
                <ul class="max-w-md divide-y divide-gray-200 dark:divide-gray-700 mt-2">
                    @foreach ($this->jobTitleList as $jobTitle)
                        <li class="pb-3 sm:pb-4">
                            <div class="flex items-center space-x-4 rtl:space-x-reverse">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $jobTitle->name }}
                                    </p>
                                </div>
                                <div
                                    class="inline-flex items-center text-base font-semibold text-gray-900 dark:text-white">
                                    <div class="inline-flex rounded-md shadow-sm" role="group">
                                        <button title="edit job title"
                                            class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-s-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white"
                                            wire:click="$dispatch('openModal', { component: 'setting.job-title', arguments: { jobTitleId: {{ $jobTitle->id }} }})">
                                            <svg class="h-5 w-5 text-teal-500" viewBox="0 0 24 24" stroke-width="2"
                                                stroke="currentColor" fill="none" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path stroke="none" d="M0 0h24v24H0z" />
                                                <path d="M9 7 h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />
                                                <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />
                                                <line x1="16" y1="5" x2="19" y2="8" />
                                            </svg>
                                        </button>
                                        <button title="remove job title"
                                            wire:confirm="You are about to delete this job title. Are you sure?"
                                            wire:loading.attr="disabled" wire:target="removeJobTitle({{ $jobTitle->id }})"
                                            class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white"
                                            wire:click="removeJobTitle({{ $jobTitle->id }})">
                                            <svg class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                wire:loading.remove wire:target="removeJobTitle({{ $jobTitle->id }})">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <x-spinner class="h-5 w-5" wire:loading wire:target="removeJobTitle({{ $jobTitle->id }})" />
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                {{ $this->jobTitleList->links() }}
            </div>


            <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 p-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Import Students
                </h3>
                @livewire('setting.import-students')
            </div>
        </div>
    @endif
    @if (Auth::user()->isAdmin())
        <div class="mb-4">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 p-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    Import Books
                </h3>
                @livewire('setting.import-book')
            </div>
        </div>
    @endif
    </div>
</div>
