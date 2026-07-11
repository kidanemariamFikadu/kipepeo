<div>
    <x-flash-toast />

    <div class="p-2 md:p-6">
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-white">Users</h2>
                <button wire:click="$dispatch('openModal', { component: 'user.create-user' })"
                    class="inline-flex items-center bg-primary-700 hover:bg-primary-800 text-white rounded-lg text-sm px-4 py-2">+ Add user</button>
            </div>

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
                            placeholder="Search name or email">
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-900 dark:text-gray-300">Role</label>
                        <select wire:model.live="admin"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2.5">
                            <option value="">All</option>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
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
                                    'name' => 'created_at',
                                    'displayName' => 'Joined',
                                ])
                                <th scope="col" class="px-4 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr wire:key="{{ $user->id }}" class="border-b dark:border-gray-700">
                                    <th scope="row"
                                        class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700 dark:bg-primary-900 dark:text-primary-200">
                                                {{ Str::of($user->name)->explode(' ')->map(fn ($p) => Str::substr($p, 0, 1))->take(2)->implode('') }}
                                            </span>
                                            {{ $user->name }}
                                        </div>
                                    </th>
                                    <td class="px-4 py-3">{{ $user->email }}</td>
                                    <td class="px-4 py-3">{{ $user->jobTitle?->name ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                                            {{ $user->isAdmin()
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200'
                                                : 'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-200' }}">
                                            {{ $user->isAdmin() ? 'Admin' : 'User' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">{{ $user->created_at->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <button title="Edit user"
                                                class="p-2 text-teal-600 hover:bg-teal-50 rounded-lg dark:text-teal-300 dark:hover:bg-gray-700"
                                                wire:click="$dispatch('openModal', { component: 'user.edit-user', arguments: { user: {{ $user->id }} }})">
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" stroke-width="2"
                                                    stroke="currentColor" fill="none" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" />
                                                    <path
                                                        d="M9 7 h-3a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-3" />
                                                    <path d="M9 15h3l8.5 -8.5a1.5 1.5 0 0 0 -3 -3l-8.5 8.5v3" />
                                                    <line x1="16" y1="5" x2="19" y2="8" />
                                                </svg>
                                            </button>
                                            <button title="Show user history"
                                                class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg dark:text-primary-300 dark:hover:bg-gray-700"
                                                wire:click="$dispatch('openModal', { component: 'user.user-history', arguments: { user: {{ $user->id }} }})">
                                                <svg class="h-5 w-5" width="24" height="24"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" />
                                                    <polyline points="12 8 12 12 14 14" />
                                                    <path d="M3.05 11a9 9 0 1 1 .5 4m-.5 5v-5h5" />
                                                </svg>
                                            </button>
                                            <button title="Reset password"
                                                class="p-2 text-amber-600 hover:bg-amber-50 rounded-lg dark:text-amber-300 dark:hover:bg-gray-700"
                                                wire:click="$dispatch('openModal', { component: 'user.reset-user-password', arguments: { user: {{ $user->id }} }})">
                                                <svg class="h-5 w-5" width="24" height="24"
                                                    viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                                                    fill="none" stroke-linecap="round" stroke-linejoin="round">
                                                    <path stroke="none" d="M0 0h24v24H0z" />
                                                    <circle cx="8" cy="15" r="4" />
                                                    <line x1="10.85" y1="12.15" x2="19" y2="4" />
                                                    <line x1="18" y1="5" x2="20" y2="7" />
                                                    <line x1="15" y1="8" x2="17" y2="10" />
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                        No users found.
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
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
