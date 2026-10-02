<div>
    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
        @if ($currentWeekBirthdays->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">No birthdays this week</p>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach ($currentWeekBirthdays as $student)
                    <li wire:key="birthday-{{ $student->id }}" class="flex items-center gap-3 py-2.5 first:pt-0 last:pb-0">
                        <x-avatar :name="$student->name" size="md" />
                        <a href="{{ route('student-detail', $student->id) }}"
                            class="min-w-0 flex-1 truncate text-sm font-medium text-gray-700 hover:text-primary-700 hover:underline focus:outline-none focus:ring-2 focus:ring-primary-500 rounded dark:text-white dark:hover:text-primary-400">
                            {{ $student->name }}
                        </a>
                        <x-badge tone="warning" size="sm" soft>
                            🎂 {{ \Carbon\Carbon::parse($student->dob)->format('M j') }}
                        </x-badge>
                    </li>
                @endforeach
            </ul>
            <div class="mt-2">{{ $currentWeekBirthdays->links(data: ['scrollTo' => false]) }}</div>
        @endif
    </div>
</div>
