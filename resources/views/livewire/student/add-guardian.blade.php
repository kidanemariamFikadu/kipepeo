<div>
    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
        <!-- Modal header -->
        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                {{ $guardian_id ? 'Edit Guardian' : 'Add Guardian' }}
            </h3>
            <x-modal-close type="button" wire:click="closeModal"
                data-modal-toggle="crud-modal" />
        </div>
        <!-- Modal body -->
        <form class="p-4 md:p-5" wire:submit="createGuardian">
            <div class="grid gap-4 mb-4 grid-cols-2">
                <div class="col-span-2 sm:col-span-1"> 
                    <label for="guardian_name"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Guardian name</label>
                    <input type="text" name="guardian_name" id="guardian_name" wire:model='addStudentGuardianForm.guardian_name'
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                        placeholder="Guardian name">
                    @error('addStudentGuardianForm.guardian_name')
                        <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-span-2 sm:col-span-1">
                    <label for="guardian_phone"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Guardian phone
                        number</label>
                    <input type="text" name="guardian_phone" id="guardian_phone" wire:model='addStudentGuardianForm.guardian_phone'
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                        placeholder="Guardian Phone">
                    @error('addStudentGuardianForm.guardian_phone')
                        <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                    @enderror
                </div>
                <div class="flex items-center ps-4 border border-gray-200 rounded dark:border-gray-700">
                    <input id="bordered-checkbox-1" type="checkbox" name="bordered-checkbox" wire:model='addStudentGuardianForm.is_primary'
                        class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <label for="bordered-checkbox-1"
                        class="w-full py-4 ms-2 text-sm font-medium text-gray-900 dark:text-gray-300">Primary contact</label>
                </div>
                <input type="hidden" wire:model="guardian_id">
                <input type="hidden" wire:model="addStudentGuardianForm.student_id">
                @error('addStudentGuardianForm.student_id')
                        <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                    @enderror
            </div>
            <x-button type="submit" wire:loading.attr="disabled" wire:target="createGuardian">
                <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    wire:loading.remove wire:target="createGuardian">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                    <polyline points="17 21 17 13 7 13 7 21" />
                    <polyline points="7 3 7 8 15 8" />
                </svg>
                <x-spinner class="h-5 w-5 text-white" wire:loading wire:target="createGuardian" />
                Save
            </x-button>
        </form>
    </div>
</div>
