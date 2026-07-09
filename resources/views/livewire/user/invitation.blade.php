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
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-white mb-4">Invitations</h2>

        <div class="mb-4">
            <div class="bg-white rounded-lg shadow dark:bg-gray-800 w-full max-w-xl">
                <div class="p-4 md:p-5 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Invite someone new
                    </h3>
                </div>
                <form class="p-4 md:p-5" wire:submit="create">
                    <div class="grid gap-4 mb-4 grid-cols-2">
                        <div class="col-span-2">
                            <label for="email"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                            <input type="email" wire:model='form.email' id="email"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                                placeholder="name@company.com">
                            @error('form.email')
                                <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-span-2 sm:col-span-1">
                            <label for="job-title"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Job
                                title</label>
                            <select id="job-title" wire:model='form.job_title_id'
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                <option value="" selected>Choose a job title</option>
                                @foreach ($this->jobTitles as $job)
                                    <option value="{{ $job->id }}">{{ $job->name }}</option>
                                @endforeach
                            </select>
                            @error('form.job_title_id')
                                <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="col-span-2 sm:col-span-1">
                            <label for="role"
                                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Role</label>
                            <select id="role" wire:model='form.role'
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                <option value="" selected>Choose a role</option>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>
                            @error('form.role')
                                <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                            @enderror
                        </div>

                        <button type="submit" wire:loading.attr="disabled" wire:target="create"
                            class="inline-flex items-center justify-center w-full text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                            <x-spinner class="h-4 w-4 mr-2 text-white" wire:loading wire:target="create" />
                            Send invite
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div>
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
                            placeholder="Search email">
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-900 dark:text-gray-300">Job title</label>
                        <select wire:model.live="jobTitleId"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2.5">
                            <option value="">All</option>
                            @foreach ($this->jobTitles as $job)
                                <option value="{{ $job->id }}">{{ $job->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                            <tr>
                                @include('livewire.includes.table-sortable-th', [
                                    'name' => 'email',
                                    'displayName' => 'Email',
                                ])
                                @include('livewire.includes.table-sortable-th', [
                                    'name' => 'job_title_id',
                                    'displayName' => 'Job title',
                                ])
                                @include('livewire.includes.table-sortable-th', [
                                    'name' => 'role',
                                    'displayName' => 'Role',
                                ])
                                @include('livewire.includes.table-sortable-th', [
                                    'name' => 'status',
                                    'displayName' => 'Status',
                                ])
                                <th scope="col" class="px-4 py-3">Invited by</th>
                                @include('livewire.includes.table-sortable-th', [
                                    'name' => 'created_at',
                                    'displayName' => 'Invited at',
                                ])
                                <th scope="col" class="px-4 py-3">Expires</th>
                                <th scope="col" class="px-4 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($invites as $invite)
                                <tr wire:key="{{ $invite->id }}" class="border-b dark:border-gray-700">
                                    <td scope="row"
                                        class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $invite->email }}</td>
                                    <td class="px-4 py-3">{{ $invite->jobTitle?->name ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                            {{ $invite->role === 'admin'
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200'
                                                : 'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-200' }}">
                                            {{ Str::title($invite->role) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                            {{ $invite->status === 'accepted'
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200'
                                                : 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200' }}">
                                            {{ Str::title($invite->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">{{ $invite->createdBy?->name ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $invite->created_at->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3">{{ $invite->expires_at ? \Carbon\Carbon::parse($invite->expires_at)->format('Y-m-d') : '—' }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <button title="Resend invite" wire:loading.attr="disabled"
                                                wire:target="sendInvitation({{ $invite->id }})"
                                                wire:click="sendInvitation({{ $invite->id }})"
                                                class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg dark:text-primary-300 dark:hover:bg-gray-700">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    wire:loading.remove wire:target="sendInvitation({{ $invite->id }})">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                                <x-spinner class="h-5 w-5" wire:loading wire:target="sendInvitation({{ $invite->id }})" />
                                            </button>
                                            <button title="Delete invite" wire:loading.attr="disabled"
                                                wire:target="deleteInvitation({{ $invite->id }})"
                                                wire:confirm="Delete the invite for {{ $invite->email }}?"
                                                wire:click="deleteInvitation({{ $invite->id }})"
                                                class="p-2 text-red-600 hover:bg-red-50 rounded-lg dark:text-red-300 dark:hover:bg-gray-700">
                                                <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                    width="24" height="24" fill="none" viewBox="0 0 24 24"
                                                    wire:loading.remove wire:target="deleteInvitation({{ $invite->id }})">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z" />
                                                </svg>
                                                <x-spinner class="h-5 w-5" wire:loading wire:target="deleteInvitation({{ $invite->id }})" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                        No invitations found.
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
                    {{ $invites->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
