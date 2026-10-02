<div>
    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
        <!-- Modal header -->
        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Reset Password for {{ $user->name }}
            </h3>
            <x-modal-close type="button" wire:click="closeModal"
                data-modal-toggle="crud-modal" />
        </div>
        <!-- Modal body -->
        <form class="p-4 md:p-5" wire:submit="resetPassword">
            <div class="grid gap-4 mb-4 grid-cols-1">
                <div>
                    <label for="password"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">New password</label>
                    <input type="password" wire:model='form.password' id="password"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                        placeholder="At least 8 characters">
                    @error('form.password')
                        <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Confirm new password</label>
                    <input type="password" wire:model='form.password_confirmation' id="password_confirmation"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                        placeholder="Confirm password">
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                {{ $user->name }} will be asked to change this password the next time they log in. Share it with them directly — no email is sent.
            </p>
            <x-button type="submit" wire:loading.attr="disabled" wire:target="resetPassword">
                <x-spinner class="h-5 w-5 mr-2 text-white" wire:loading wire:target="resetPassword" />
                Reset Password
            </x-button>
        </form>
    </div>
</div>
