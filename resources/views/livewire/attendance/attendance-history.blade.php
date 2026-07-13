<div>
    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
        <!-- Modal header -->
        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Attendance History of {{ $student?->name }}
            </h3>
            <button type="button" wire:click="closeModal"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                data-modal-toggle="crud-modal">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
        </div>

        <!-- Modal body -->
        <div class="p-4 md:p-5">
            <!-- Date navigation -->
            <div class="flex items-center gap-2 mb-4">
                <button type="button" wire:click="previousDay"
                    class="inline-flex items-center justify-center w-9 h-9 shrink-0 rounded-lg border border-gray-300 text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:border-gray-500 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white"
                    title="Previous day">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    <span class="sr-only">Previous day</span>
                </button>

                <input type="date" id="date" wire:model.live="date" max="{{ now()->format('Y-m-d') }}"
                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500
                        block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">

                <button type="button" wire:click="nextDay" @disabled($this->isToday())
                    class="inline-flex items-center justify-center w-9 h-9 shrink-0 rounded-lg border border-gray-300 text-gray-500 hover:bg-gray-100 hover:text-gray-900 disabled:opacity-40 disabled:hover:bg-transparent dark:border-gray-500 dark:text-gray-300 dark:hover:bg-gray-600 dark:hover:text-white"
                    title="Next day">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="sr-only">Next day</span>
                </button>

                @unless ($this->isToday())
                    <button type="button" wire:click="goToToday"
                        class="shrink-0 text-sm font-medium text-primary-700 hover:underline dark:text-primary-400">
                        Today
                    </button>
                @endunless

                <div wire:loading wire:target="date,previousDay,nextDay,goToToday" class="shrink-0">
                    <x-spinner class="h-4 w-4 text-gray-400" />
                </div>

                @error('date')
                    <span class="text-red-500 text-xs block">{{ $message }}</span>
                @enderror
            </div>

            @if (!$attendance)
                <div class="flex flex-col items-center gap-2 text-center py-10">
                    <svg class="w-10 h-10 text-gray-300 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400">
                        No check-ins on {{ \Carbon\Carbon::parse($date)->format('D, M j Y') }}
                    </p>
                </div>
            @else
                @php
                    $totalSeconds = $attendance->attrs->sum(function ($attr) {
                        $start = \Carbon\Carbon::createFromFormat('H:i:s', $attr->time_in);
                        $end = $attr->time_out
                            ? \Carbon\Carbon::createFromFormat('H:i:s', $attr->time_out)
                            : now();

                        return $end->diffInSeconds($start, true);
                    });

                    if (! function_exists('secondsToHms')) {
                        function secondsToHms($seconds)
                        {
                            $hours = floor($seconds / 3600);
                            $minutes = floor(($seconds % 3600) / 60);
                            $seconds = $seconds % 60;

                            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                        }
                    }
                @endphp

                <!-- Day summary -->
                <div class="grid grid-cols-2 gap-3 mb-4">
                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <p class="text-xl font-semibold text-gray-900 dark:text-white">{{ secondsToHms($totalSeconds) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Total time this day</p>
                    </div>
                    <div class="p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                        <p class="text-xl font-semibold text-gray-900 dark:text-white">
                            {{ $attendance->attrs->count() }}
                            <span class="text-sm font-normal text-gray-500 dark:text-gray-400">
                                {{ Str::plural('session', $attendance->attrs->count()) }}
                            </span>
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            @if ($attendance->current_in)
                                <span class="inline-flex items-center gap-1 text-green-600 dark:text-green-400">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Currently checked in
                                </span>
                            @else
                                Checked out
                            @endif
                        </p>
                    </div>
                </div>

                <div class="relative overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">Time In</th>
                                <th scope="col" class="px-6 py-3">Time Out</th>
                                <th scope="col" class="px-6 py-3">Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($attendance->attrs as $attr)
                                <tr @class([
                                    'border-b dark:border-gray-700',
                                    'bg-green-50 dark:bg-green-950' => !$attr->time_out,
                                    'bg-white dark:bg-gray-800' => $attr->time_out,
                                ])>
                                    <th scope="row"
                                        class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $attr->time_in }}
                                    </th>
                                    <td class="px-6 py-4 break-words">
                                        {{ $attr->time_out ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 break-words">
                                        @if ($attr->time_out)
                                            @php
                                                $startDateTime = \Carbon\Carbon::createFromFormat('H:i:s', $attr->time_in);
                                                $endDateTime = \Carbon\Carbon::createFromFormat('H:i:s', $attr->time_out);
                                                $timeDifferenceInSeconds = $endDateTime->diffInSeconds($startDateTime, true);
                                            @endphp
                                            {{ secondsToHms($timeDifferenceInSeconds) }}
                                        @else
                                            @php
                                                $startDateTime = \Carbon\Carbon::createFromFormat('H:i:s', $attr->time_in);
                                                $timeDifferenceInSeconds = now()->diffInSeconds($startDateTime, true);
                                            @endphp
                                            <span class="inline-flex items-center gap-1 text-green-700 dark:text-green-400 font-medium">
                                                {{ secondsToHms($timeDifferenceInSeconds) }} · in progress
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
