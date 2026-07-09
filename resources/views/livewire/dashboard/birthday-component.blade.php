<div>
    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
        @if ($currentWeekBirthdays->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">No birthdays this week</p>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach ($currentWeekBirthdays as $student)
                    <li class="flex items-center gap-3 py-2.5 first:pt-0 last:pb-0">
                        <span
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-semibold text-primary-700 dark:bg-primary-900 dark:text-primary-200">
                            {{ Str::of($student->name)->explode(' ')->map(fn ($p) => Str::substr($p, 0, 1))->take(2)->implode('') }}
                        </span>
                        <span class="min-w-0 flex-1 truncate text-sm font-medium text-gray-700 dark:text-white">
                            {{ $student->name }}
                        </span>
                        <span
                            class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                            🎂 {{ \Carbon\Carbon::parse($student->dob)->format('M j') }}
                        </span>
                    </li>
                @endforeach
            </ul>
            <div class="mt-2">{{ $currentWeekBirthdays->links() }}</div>
        @endif
    </div>
</div>
