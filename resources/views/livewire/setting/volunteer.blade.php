<div>
    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
        <!-- Modal header -->
        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                @if (!$this->volunteerId)
                    Add volunteer
                @else
                    Edit volunteer
                @endif
            </h3>
            <button type="button" wire:click="closeModal"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                data-modal-toggle="crud-modal">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
        </div>
        <!-- Modal body -->
        <form class="p-4 md:p-5" wire:submit="saveVolunteer">

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Error</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="grid gap-4 mb-4 grid-cols-2">
                <div class="col-span-2">
                    <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
                    <input type="text" name="name" id="name" wire:model='name'
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                        placeholder="Volunteer name">
                    @error('name')
                        <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Phone</label>
                    <input type="text" name="phone" id="phone" wire:model='phone'
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                        placeholder="Phone">
                    @error('phone')
                        <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                    <input type="email" name="email" id="email" wire:model='email'
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                        placeholder="Email">
                    @error('email')
                        <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-span-2">
                    <label for="status" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Status</label>
                    <select id="status" wire:model='status'
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        @foreach ($this->statuses() as $case)
                            <option value="{{ $case->value }}">{{ ucfirst($case->value) }}</option>
                        @endforeach
                    </select>
                    @error('status')
                        <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                    @enderror
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Deactivating a volunteer removes them from the check-in list but keeps their history.
                    </p>
                </div>
                <div class="col-span-2">
                    <label for="notes" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Notes</label>
                    <textarea name="notes" id="notes" wire:model='notes' rows="3"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                        placeholder="e.g. availability, specialty"></textarea>
                    @error('notes')
                        <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <button type="submit" wire:loading.attr="disabled" wire:target="saveVolunteer"
                class="text-white inline-flex items-center bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
                <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    wire:loading.remove wire:target="saveVolunteer">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                    <polyline points="17 21 17 13 7 13 7 21" />
                    <polyline points="7 3 7 8 15 8" />
                </svg>
                <x-spinner class="h-5 w-5 text-white" wire:loading wire:target="saveVolunteer" />
                Save
            </button>
        </form>
    </div>
</div>
