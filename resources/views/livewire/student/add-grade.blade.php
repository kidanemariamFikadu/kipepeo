<div>
    <x-flash-toast />

    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
        <!-- Modal header -->
        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Add Grade
            </h3>
            <x-modal-close type="button" wire:click="closeModal"
                data-modal-toggle="crud-modal" />
        </div>
        <!-- Modal body -->
        <form class="p-4 md:p-5" wire:submit="createGrade">
            <div class="grid gap-4 mb-4 grid-cols-2">
                <div class="col-span-2 sm:col-span-1">
                    <label for="grade"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Grade</label>
                    <select id="grade" wire:model='grade'
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                        <option value="" selected>Choose a grade</option>
                        @foreach ($this->grades as $grade)
                            <option value="{{ $grade->id }}">{{ $grade->grade }}</option>
                        @endforeach
                    </select>
                    @error('grade')
                        <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                    @enderror
                </div>
            </div>
            <x-button type="submit" wire:loading.attr="disabled" wire:target="createGrade">
                <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    wire:loading.remove wire:target="createGrade">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                    <polyline points="17 21 17 13 7 13 7 21" />
                    <polyline points="7 3 7 8 15 8" />
                </svg>
                <x-spinner class="h-5 w-5 text-white" wire:loading wire:target="createGrade" />
                Save
            </x-button>
        </form>
    </div>
</div>
