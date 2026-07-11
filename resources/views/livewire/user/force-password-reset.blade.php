<div class="w-full max-w-sm p-4 bg-white border border-gray-200 rounded-lg mx-auto shadow sm:p-6 md:p-8 dark:bg-gray-800 dark:border-gray-700">
    <form class="space-y-6" wire:submit="updatePassword">
        <div>
            <h5 class="text-xl font-medium text-gray-900 dark:text-white">Set a new password</h5>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                An administrator created this account for you. Enter the password they gave you, then choose a new one only you know.
            </p>
        </div>

        <div>
            <label for="current_password"
                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Current password</label>
            <input type="password" wire:model='state.current_password' id="current_password"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                placeholder="Password given to you by an admin" autocomplete="current-password">
            @error('current_password')
                <span class="text-red-500 text-xs mt-3 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="password"
                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">New password</label>
            <input type="password" wire:model='state.password' id="password"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                placeholder="At least 8 characters" autocomplete="new-password">
            @error('password')
                <span class="text-red-500 text-xs mt-3 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="password_confirmation"
                class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Confirm new password</label>
            <input type="password" wire:model='state.password_confirmation' id="password_confirmation"
                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white"
                placeholder="Confirm password" autocomplete="new-password">
        </div>

        <div class="flex items-center justify-end">
            <button type="submit" wire:loading.attr="disabled" wire:target="updatePassword"
                class="inline-flex items-center px-4 py-2 bg-primary-700 hover:bg-primary-800 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50">
                <x-spinner class="h-4 w-4 mr-2 text-white" wire:loading wire:target="updatePassword" />
                {{ __('Set new password') }}
            </button>
        </div>
    </form>
</div>
