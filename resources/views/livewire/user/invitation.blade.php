<div>

    @if (session('success'))
        <div id="alert-border-3"
            class="flex items-center p-4 mb-4 text-green-800 border-t-4 border-green-300 bg-green-50 dark:text-green-400 dark:bg-gray-800 dark:border-green-800"
            role="alert">
            <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                viewBox="0 0 20 20">
                <path
                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
            </svg>
            <div class="ms-3 text-sm font-medium">
                <span class="text-green-500 text-xs">{{ session('success') }}</span>
            </div>
            <button type="button"
                class="ms-auto -mx-1.5 -my-1.5 bg-green-50 text-green-500 rounded-lg focus:ring-2 focus:ring-green-400 p-1.5 hover:bg-green-200 inline-flex items-center justify-center h-8 w-8 dark:bg-gray-800 dark:text-green-400 dark:hover:bg-gray-700"
                data-dismiss-target="#alert-border-3" aria-label="Close">
                <span class="sr-only">Dismiss</span>
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
            </button>
        </div>
    @endif

    
    <div>
        <div
            class="w-full max-w-sm p-4 bg-white border border-gray-200 rounded-lg mx-auto shadow sm:p-6 md:p-8 dark:bg-gray-800 dark:border-gray-700">
            <form class="space-y-6" wire:submit="create">
                <h5 class="text-xl font-medium text-gray-900 dark:text-white">Invite User</h5>

                <div>
                    <label for="email"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                    <input type="email" wire:model='form.email' id="email"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                        placeholder="name@company.com">
                    @error('form.email')
                        <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="job-title" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Job
                        title</label>
                    <select id="job-title" wire:model='form.job_title_id'
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option selected>Choose a job title</option>
                        @foreach ($this->jobTitles as $job)
                            <option value="{{ $job->id }}">{{ $job->name }}</option>
                        @endforeach
                    </select>
                    @error('form.job_title_id')
                        <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="role"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Role</label>
                    <select id="role" wire:model='form.role'
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option selected>Choose a role</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                    @error('form.role')
                        <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit"  wire:loading.attr="disabled"
                    class="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Invite</button>
            </form>
        </div>
    </div>

    <div class="mt-10 p-5 mx-auto">
        {{-- <button class="block mt-3 px-4 py-2 bg-teal-500">Search</button> --}}

        <div>
            <h2 class="text-2xl mb-3">Invitations</h2>

            <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                <div
                    class="flex flex-column sm:flex-row flex-wrap space-y-4 sm:space-y-0 items-center justify-between pb-4">
                    <div>
                        {{-- <a href="user-create" class="px-3 py-1 bg-teal-500 text-white rounded">+ Create User</a> --}}
                    </div>
                    <label for="table-search" class="sr-only">Search</label>
                    <div class="relative">
                        <div
                            class="absolute inset-y-0 left-0 rtl:inset-r-0 rtl:right-0 flex items-center ps-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" aria-hidden="true" fill="currentColor"
                                viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <input type="text" wire:model.live.debounce.500ms="search"
                            class="block p-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg w-80 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            placeholder="Search for items">
                    </div>
                </div>
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">Email</th>
                            <th scope="col" class="px-6 py-3">Job title</th>
                            <th scope="col" class="px-6 py-3">Role</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3">Invited by</th>
                            <th scope="col" class="px-6 py-3">Created At</th>
                            <th scope="col" class="px-6 py-3">Expires At</th>
                            <th scope="col" class="px-6 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->invites as $invite)
                            <tr class="border-b border-gray-100">
                                <td class="px-3 py-1">{{ $invite->email }}</td>
                                <td class="px-3 py-1">{{ $invite->jobTitle?->name }}</td>
                                <td class="px-3 py-1">{{ $invite->role }}</td>
                                <td class="px-3 py-1">{{ Str::title($invite->status) }}</td>
                                <td class="px-3 py-1">{{ $invite->createdBy->name }}</td>
                                <td class="px-3 py-1">{{ $invite->created_at->format('d-m-Y') }}</td>
                                <td class="px-3 py-1">{{ $invite->expires_at }}</td>
                                <td class="px-3 py-1">
                                    <button class="rounded px-3 py-1 bg-green-400 text-white" wire:loading.attr="disabled"
                                        wire:click="sendInvitation({{ $invite->id }})">Resend</button>
                                    <button class="rounded px-3 py-1 bg-red-400 text-white" wire:loading.attr="disabled"
                                        wire:click="deleteInvitation({{ $invite->id }})">Delete</button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $this->invites->links() }}

            {{-- <x-modal></x-modal> --}}
        </div>
    </div>
