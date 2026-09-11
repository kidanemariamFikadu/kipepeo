<div>
    <x-flash-toast />

    <div class="p-2 md:p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-white">Backup</h2>
            <a href="{{ route('settings') }}" class="text-sm text-primary-700 dark:text-primary-300 hover:underline">
                &larr; Back to settings
            </a>
        </div>

        <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 p-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Download a backup</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Downloads a single .zip containing a full database dump (<code class="text-xs">database.sql</code>)
                and the server's current <code class="text-xs">.env</code> configuration file. Keep it somewhere
                safe -- the .env file contains database and mail credentials.
            </p>

            @if ($error)
                <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700 dark:bg-red-900/40 dark:text-red-300">
                    {{ $error }}
                </div>
            @endif

            <button wire:click="downloadBackup" wire:loading.attr="disabled" wire:target="downloadBackup"
                class="inline-flex items-center bg-primary-700 hover:bg-primary-800 text-white rounded-lg text-sm px-4 py-2.5 disabled:opacity-50">
                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"
                    wire:loading.remove wire:target="downloadBackup">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                </svg>
                <x-spinner class="h-5 w-5 mr-2 text-white" wire:loading wire:target="downloadBackup" />
                <span wire:loading.remove wire:target="downloadBackup">Download backup</span>
                <span wire:loading wire:target="downloadBackup">Building backup&hellip;</span>
            </button>
        </div>

        <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 p-4 mt-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Restoring</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                There's no restore button here on purpose -- restoring overwrites this server's database and
                <code class="text-xs">.env</code>, so it's only available from a fresh install with no users yet.
                On the target server, visit <code class="text-xs">{{ url('/restore') }}</code> and it will offer to
                restore from a backup file; once any user exists, that page stops working.
            </p>
        </div>
    </div>
</div>
