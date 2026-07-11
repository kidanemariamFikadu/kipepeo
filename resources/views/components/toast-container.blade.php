<div
    x-data="{ show: false, type: 'success', message: '', timer: null }"
    x-on:flash-toast.window="
        type = $event.detail.type;
        message = $event.detail.message;
        show = true;
        clearTimeout(timer);
        timer = setTimeout(() => show = false, 5000);
    "
    x-show="show"
    x-transition:enter="transform ease-out duration-300 transition"
    x-transition:enter-start="translate-y-4 opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0 translate-y-2"
    style="display: none;"
    class="fixed bottom-4 right-4 z-[70] w-full max-w-sm"
>
    <div class="flex items-start gap-3 rounded-xl bg-white p-4 shadow-lg ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10"
        role="alert">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
            :class="type === 'success'
                ? 'bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-300'
                : 'bg-red-100 text-red-600 dark:bg-red-900 dark:text-red-300'">
            <svg x-show="type === 'success'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
            <svg x-show="type !== 'success'" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display: none;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </span>

        <div class="min-w-0 flex-1 pt-0.5">
            <p class="text-sm font-semibold text-gray-900 dark:text-white"
                x-text="type === 'success' ? 'Success' : 'Error'"></p>
            <p class="mt-0.5 break-words text-sm text-gray-600 dark:text-gray-300" x-text="message"></p>
        </div>

        <button
            type="button"
            @click="show = false"
            class="shrink-0 rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200"
            aria-label="Dismiss"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>
