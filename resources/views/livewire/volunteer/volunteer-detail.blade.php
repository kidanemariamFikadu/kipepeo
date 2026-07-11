<div>
    @if (session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 mb-3 rounded relative" role="alert">
            <strong class="font-bold">Error</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @elseif (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 mb-3 rounded relative" role="alert">
            <strong class="font-bold">Success</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    <div class="p-2 md:p-6">
        <a href="{{ route('volunteers') }}"
            class="inline-flex items-center gap-1 mb-4 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Volunteers
        </a>

        <div class="flex flex-wrap items-center gap-2 mb-4">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-white">{{ $volunteerDetails->name }}</h2>
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold
                {{ $volunteerDetails->isActive()
                    ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200'
                    : 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' }}">
                {{ $volunteerDetails->isActive() ? 'Active' : 'Inactive' }}
            </span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 p-4">
                <div class="flex items-center justify-between p-4 md:p-5 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Basic Information
                    </h3>
                </div>
                <form class="p-4 md:p-5" wire:submit="update">
                    <div class="grid gap-4 mb-4 grid-cols-2">
                        <div class="col-span-2">
                            <label for="name" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
                            <input type="text" wire:model="name" id="name"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Jane Deo">
                            @error('name')
                                <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label for="phone" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Phone</label>
                            <input type="text" wire:model="phone" id="phone"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            @error('phone')
                                <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label for="email" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Email</label>
                            <input type="email" wire:model="email" id="email"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            @error('email')
                                <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label for="status" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Status</label>
                            <select id="status" wire:model="status"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                @foreach ($this->statuses() as $case)
                                    <option value="{{ $case->value }}">{{ ucfirst($case->value) }}</option>
                                @endforeach
                            </select>
                            @error('status')
                                <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-span-2 sm:col-span-1">
                            <label for="hourlyRate" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Hourly Rate (KSH)</label>
                            <input type="number" step="0.01" min="0" wire:model="hourlyRate" id="hourlyRate"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            @error('hourlyRate')
                                <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-span-2">
                            <label for="notes" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Notes</label>
                            <textarea wire:model="notes" id="notes" rows="3"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"></textarea>
                            @error('notes')
                                <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <button type="submit" wire:loading.attr="disabled" wire:target="update"
                        class="text-white inline-flex items-center bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 disabled:opacity-50">
                        <x-spinner class="h-5 w-5 text-white mr-1.5" wire:loading wire:target="update" />
                        Save
                    </button>
                </form>
            </div>

            <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 p-4">
                <div class="flex items-center justify-between p-4 md:p-5 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Earnings Summary
                    </h3>
                </div>
                <div class="p-4 md:p-5">
                    <form wire:submit="calculateEarnings" class="flex flex-wrap items-end gap-3 mb-4">
                        <div>
                            <label for="earningsFromDate" class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-400">From</label>
                            <input type="date" id="earningsFromDate" wire:model="earningsFromDate"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                        </div>
                        <div>
                            <label for="earningsToDate" class="block mb-1 text-xs font-medium text-gray-700 dark:text-gray-400">To</label>
                            <input type="date" id="earningsToDate" wire:model="earningsToDate"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                        </div>
                        <button type="submit" wire:loading.attr="disabled" wire:target="calculateEarnings"
                            class="inline-flex items-center p-2 px-4 bg-primary-700 hover:bg-primary-800 text-white rounded-lg text-sm disabled:opacity-50">
                            Update
                        </button>
                    </form>
                    @error('earningsFromDate')
                        <span class="text-red-500 text-xs mb-3 block">{{ $message }}</span>
                    @enderror
                    @error('earningsToDate')
                        <span class="text-red-500 text-xs mb-3 block">{{ $message }}</span>
                    @enderror

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Days Present</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $earningsDaysPresent }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Total Hours</p>
                            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $volunteerDetails->secondsToHms($earningsTotalSeconds) }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-xs text-gray-500 dark:text-gray-400">Estimated Earnings</p>
                            @if ($estimatedEarnings !== null)
                                <p class="text-xl font-bold text-gray-900 dark:text-white">KSH {{ number_format($estimatedEarnings, 2) }}</p>
                            @else
                                <p class="text-sm text-gray-400 dark:text-gray-500">No hourly rate set</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 p-4">
                <div class="flex items-center justify-between p-4 md:p-5 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Attendance History
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-700 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-4 py-3">Date</th>
                                <th scope="col" class="px-4 py-3">Time In / Out</th>
                                <th scope="col" class="px-4 py-3">Total Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($volunteerDetails->attendances as $attendance)
                                <tr wire:key="attendance-{{ $attendance->id }}" class="border-b dark:border-gray-700">
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                        {{ \Carbon\Carbon::parse($attendance->date)->format('Y-m-d') }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @forelse ($attendance->attrs as $attr)
                                            <div>{{ \Carbon\Carbon::parse($attr->time_in)->format('H:i') }} &ndash; {{ $attr->time_out ? \Carbon\Carbon::parse($attr->time_out)->format('H:i') : 'Still in' }}</div>
                                        @empty
                                            <span class="text-gray-400 dark:text-gray-500">&mdash;</span>
                                        @endforelse
                                    </td>
                                    <td class="px-4 py-3">{{ $volunteerDetails->secondsToHms($attendance->total_time) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                        No attendance recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($volunteerDetails->attendances->count() >= 15)
                    <p class="px-4 pt-2 text-xs text-gray-400 dark:text-gray-500">
                        Showing most recent 15 &mdash; see the <a href="{{ route('report') }}" class="underline hover:text-primary-600 dark:hover:text-primary-400">Volunteer Activity report</a> for full history.
                    </p>
                @endif
            </div>

            <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 p-4">
                <div class="flex items-center justify-between p-4 md:p-5 border-b dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Activity Log
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-700 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-4 py-3">Date</th>
                                <th scope="col" class="px-4 py-3">Duty</th>
                                <th scope="col" class="px-4 py-3">Students</th>
                                <th scope="col" class="px-4 py-3">Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($volunteerDetails->activities as $activity)
                                <tr wire:key="activity-{{ $activity->id }}" class="border-b dark:border-gray-700">
                                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($activity->date)->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $activity->activityType?->name ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $activity->students->pluck('name')->implode(', ') ?: '—' }}</td>
                                    <td class="px-4 py-3">{{ $activity->notes ?: '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                        No activities logged yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($volunteerDetails->activities->count() >= 20)
                    <p class="px-4 pt-2 text-xs text-gray-400 dark:text-gray-500">
                        Showing most recent 20 &mdash; see the <a href="{{ route('report') }}" class="underline hover:text-primary-600 dark:hover:text-primary-400">Volunteer Activity report</a> for full history.
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
