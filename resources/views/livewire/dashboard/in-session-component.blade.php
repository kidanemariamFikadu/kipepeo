<div>
    <div class="p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
        @if (count($inSessionStudents) > 0)
            <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach ($inSessionStudents as $attendance)
                    @php
                        $dob = \Carbon\Carbon::parse($attendance->student->dob);
                        $isBirthday = $dob->isBirthday() && $dob->age > 0;
                    @endphp
                    <li class="flex items-center gap-3 py-2.5 first:pt-0 last:pb-0">
                        <span class="relative flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-semibold text-primary-700 dark:bg-primary-900 dark:text-primary-200">
                            {{ Str::of($attendance->student->name)->explode(' ')->map(fn ($p) => Str::substr($p, 0, 1))->take(2)->implode('') }}
                            <span class="absolute -bottom-0.5 -right-0.5 h-2.5 w-2.5 rounded-full bg-green-500 ring-2 ring-white dark:ring-gray-800"
                                title="Currently checked in"></span>
                        </span>
                        <span class="min-w-0 flex-1 truncate text-sm font-medium text-gray-700 dark:text-white">
                            {{ $attendance->student->name }}
                        </span>
                        @if ($isBirthday)
                            <span class="text-xs">🎂</span>
                        @endif
                        <button title="Check out"
                            wire:loading.attr="disabled" wire:target="checkOut({{ $attendance->id }})"
                            class="shrink-0 rounded-full p-1.5 text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:text-red-300 dark:hover:bg-red-950"
                            wire:click="checkOut({{ $attendance->id }})">
                            <svg class="h-4 w-4" width="24" height="24" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"
                                wire:loading.remove wire:target="checkOut({{ $attendance->id }})">
                                <path stroke="none" d="M0 0h24v24H0z" />
                                <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" />
                                <path d="M7 12h14l-3 -3m0 6l3 -3" />
                            </svg>
                            <x-spinner class="h-4 w-4" wire:loading wire:target="checkOut({{ $attendance->id }})" />
                        </button>
                    </li>
                @endforeach
            </ul>
            <div class="mt-2">{{ $inSessionStudents->links() }}</div>
        @else
            <p class="text-sm text-gray-500 dark:text-gray-400">No students currently in session</p>
        @endif
    </div>
</div>
