<div class="w-full max-w-md p-4 bg-white border border-gray-200 rounded-lg mx-auto shadow sm:p-6 md:p-8 dark:bg-gray-800 dark:border-gray-700">
    @if ($done)
        <div class="text-center">
            <span class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-300">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </span>
            <h5 class="text-xl font-medium text-gray-900 dark:text-white">Restore complete</h5>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                The database and configuration have been restored. This page will not be reachable again now that
                accounts exist.
            </p>
            <a href="{{ url('/') }}"
                class="mt-4 inline-flex items-center px-4 py-2 bg-primary-700 hover:bg-primary-800 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest">
                Go to sign in
            </a>
        </div>
    @else
        <form class="space-y-6" wire:submit="restore">
            <div>
                <h5 class="text-xl font-medium text-gray-900 dark:text-white">Restore from backup</h5>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    No accounts exist on this install yet, so you can restore a previous backup instead of starting
                    from scratch. Upload the .zip downloaded from Settings &gt; Backup on another install.
                </p>
            </div>

            <div class="rounded-lg bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">
                This overwrites this server's database and <code class="text-xs">.env</code> file. The current
                .env is saved to <code class="text-xs">storage/app/backups/</code> first, just in case.
            </div>

            <div>
                <label for="backupFile" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Backup
                    file (.zip)</label>
                <input type="file" wire:model="backupFile" id="backupFile" accept=".zip"
                    class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none dark:text-gray-400 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400">
                <div wire:loading wire:target="backupFile" class="mt-1 text-xs text-gray-500 dark:text-gray-400">Uploading&hellip;</div>
                @error('backupFile')
                    <span class="text-red-500 text-xs mt-2 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" wire:loading.attr="disabled" wire:target="restore,backupFile"
                    class="inline-flex items-center px-4 py-2 bg-primary-700 hover:bg-primary-800 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50">
                    <x-spinner class="h-4 w-4 mr-2 text-white" wire:loading wire:target="restore" />
                    {{ __('Restore') }}
                </button>
            </div>
        </form>
    @endif
</div>
