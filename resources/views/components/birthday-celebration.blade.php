{{--
    Global listener for the 'student-birthday-checkin' event dispatched from
    the check-in flows (AttendanceStudent::checkIn, QuickCheckInStudents::checkIn).
    Lives at the layout root (not inside the check-in modal) for the same
    reason toasts do -- the wire-elements modal wrapper clips position:fixed
    children, see flash-toast.blade.php.
--}}
<div
    x-data="{ show: false, name: '', timer: null }"
    x-on:student-birthday-checkin.window="
        name = $event.detail.name;
        show = true;
        window.KipepeoFireworks && window.KipepeoFireworks.play(name);
        clearTimeout(timer);
        timer = setTimeout(() => show = false, 4000);
    "
    x-show="show"
    x-transition:enter="transform ease-out duration-300 transition"
    x-transition:enter-start="scale-90 opacity-0"
    x-transition:enter-end="scale-100 opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    style="display: none;"
    class="fixed top-6 left-1/2 -translate-x-1/2 z-[9999] pointer-events-none"
>
    <div class="flex items-center gap-3 rounded-full bg-white px-5 py-3 shadow-lg ring-1 ring-black/5 dark:bg-gray-800 dark:ring-white/10">
        <span class="text-2xl">🎉</span>
        <p class="text-sm font-semibold text-gray-900 dark:text-white">
            Happy Birthday, <span x-text="name"></span>!
        </p>
        <span class="text-2xl">🎂</span>
    </div>
</div>
