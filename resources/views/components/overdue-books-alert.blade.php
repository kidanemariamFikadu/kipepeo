{{--
    Global listener for the 'student-overdue-books' event dispatched from the
    check-in flows (AttendanceStudent::checkIn, QuickCheckInStudents::checkIn).
    Sits below <x-birthday-celebration /> (top-24 vs top-6) so both can show
    at once without overlapping -- lives at the layout root for the same
    modal-clipping reason as the toast container, see flash-toast.blade.php.
--}}
<div
    x-data="{ show: false, name: '', books: [], timer: null }"
    x-on:student-overdue-books.window="
        name = $event.detail.name;
        books = $event.detail.books;
        show = true;
        clearTimeout(timer);
        timer = setTimeout(() => show = false, 8000);
    "
    x-show="show"
    x-transition:enter="transform ease-out duration-300 transition"
    x-transition:enter-start="translate-y-2 opacity-0"
    x-transition:enter-end="translate-y-0 opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    style="display: none;"
    class="fixed top-24 left-1/2 -translate-x-1/2 z-[9998] w-full max-w-sm"
>
    <div class="flex items-start gap-3 rounded-xl bg-white p-4 shadow-lg ring-1 ring-amber-300 dark:bg-gray-800 dark:ring-amber-700"
        role="alert">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600 dark:bg-amber-900 dark:text-amber-300">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008zM21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </span>

        <div class="min-w-0 flex-1 pt-0.5">
            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                <span x-text="name"></span> has overdue book<span x-text="books.length > 1 ? 's' : ''"></span>
            </p>
            <ul class="mt-1 space-y-0.5">
                <template x-for="book in books" :key="book">
                    <li class="text-sm text-gray-600 dark:text-gray-300" x-text="book"></li>
                </template>
            </ul>
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
