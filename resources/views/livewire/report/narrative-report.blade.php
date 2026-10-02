<div class="p-2 md:p-6">
    <div class="flex items-center justify-between mb-4 no-print">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-white">
            Narrative Report
        </h2>
        <button type="button" onclick="window.print()"
            class="inline-flex items-center p-2 px-4 bg-teal-600 hover:bg-teal-700 text-white rounded-lg text-sm">
            <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" />
            </svg>
            Print
        </button>
    </div>

    <div class="mb-6 no-print grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
        <div>
            <label for="reportType" class="block text-sm font-medium text-gray-700 dark:text-gray-400">Report</label>
            <x-select id="reportType" wire:model.live="reportType" class="block w-full p-2 mt-1">
                @foreach (\App\Livewire\Report\NarrativeReport::TYPES as $value => $label)
                    <option wire:key="type-{{ $value }}" value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </x-select>
        </div>

        <div>
            <label for="fromDate" class="block text-sm font-medium text-gray-700 dark:text-gray-400">From</label>
            <x-input id="fromDate" type="date" wire:model.live="fromDate" class="block w-full p-2 mt-1" />
            @error('fromDate')
                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="toDate" class="block text-sm font-medium text-gray-700 dark:text-gray-400">To</label>
            <x-input id="toDate" type="date" wire:model.live="toDate" class="block w-full p-2 mt-1" />
            @error('toDate')
                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center gap-2">
            <x-secondary-button type="button" wire:click="setLastFullMonth" class="text-xs">Last full month</x-secondary-button>
            <x-secondary-button type="button" wire:click="setThisTerm" class="text-xs">Last 3 months</x-secondary-button>
            <span wire:loading wire:target="reportType,fromDate,toDate,setLastFullMonth,setThisTerm"
                class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
                <x-spinner class="h-4 w-4" />
            </span>
        </div>
    </div>

    @if ($snapshot === null)
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-800 dark:bg-amber-900/30 dark:border-amber-800 dark:text-amber-200">
            Choose a period with a start date on or before its end date to see what would be sent.
        </div>
    @else
        {{-- The point of this page: what leaves the building, stated before
             anyone is asked to approve the sending. --}}
        <div class="no-print mb-6 bg-primary-50 border border-primary-200 rounded-lg p-4 dark:bg-primary-900/20 dark:border-primary-800">
            <h3 class="text-sm font-semibold text-primary-900 dark:text-primary-100">What would be sent</h3>
            <p class="mt-1 text-sm text-primary-800 dark:text-primary-200">
                Only the figures below. No child's name, date of birth or guardian contact is included, and
                none is sent even when you are looking at a single school or grade.
            </p>
            <p class="mt-2 text-sm text-primary-800 dark:text-primary-200">
                @if ($reportType === 'donor')
                    <span class="font-medium">Small groups are merged.</span>
                    A school or grade with fewer than 5 children is folded into an "Other" row, because a
                    named school beside a count of two can point at a particular child.
                @else
                    <span class="font-medium">Small groups are shown in full.</span>
                    This is the internal view, so no merging is applied — do not paste these figures into
                    anything that leaves the organisation.
                @endif
            </p>
        </div>

        <div class="printable relative">
            <div wire:loading.class="opacity-40 pointer-events-none"
                wire:target="reportType,fromDate,toDate,setLastFullMonth,setThisTerm" class="transition-opacity">

                <x-report.print-header title="Narrative Report"
                    :subtitle="\App\Livewire\Report\NarrativeReport::TYPES[$reportType] . ' — ' . $snapshot['period']['from'] . ' to ' . $snapshot['period']['to']" />

                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Visits</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($snapshot['attendance']['visits']) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $snapshot['period']['weekdays'] }} weekdays in period</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Children attending</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($snapshot['attendance']['unique_students']) }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Average visit</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ $snapshot['attendance']['average_visit_minutes'] === null ? '—' : $snapshot['attendance']['average_visit_minutes'] . ' min' }}
                        </p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Enrolled at period end</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white">
                            {{ $snapshot['enrollment']['total_active'] ?? '—' }}
                        </p>
                        @isset($snapshot['enrollment']['distinct_schools'])
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">from {{ $snapshot['enrollment']['distinct_schools'] }} schools</p>
                        @endisset
                    </div>
                </div>

                @if ($deltas !== [])
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-3">
                            Compared with {{ $snapshot['previous_period']['period']['from'] }} to {{ $snapshot['previous_period']['period']['to'] }}
                        </h3>
                        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            @foreach ($deltas as $delta)
                                <div wire:key="delta-{{ $loop->index }}">
                                    <dt class="text-sm text-gray-500 dark:text-gray-400">{{ $delta['label'] }}</dt>
                                    <dd class="text-lg font-semibold text-gray-900 dark:text-white">
                                        {{ number_format($delta['now']) }}
                                        <span class="text-sm font-normal {{ $delta['change'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                            {{ $delta['change'] >= 0 ? '+' : '' }}{{ number_format($delta['change']) }}
                                            ({{ $delta['percent'] >= 0 ? '+' : '' }}{{ $delta['percent'] }}%)
                                        </span>
                                    </dd>
                                    <dd class="text-xs text-gray-500 dark:text-gray-400">was {{ number_format($delta['then']) }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @elseif (isset($snapshot['previous_period']))
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6 text-sm text-gray-600 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300">
                        No comparable activity in the preceding period
                        ({{ $snapshot['previous_period']['period']['from'] }} to {{ $snapshot['previous_period']['period']['to'] }}),
                        so this report can describe the period but not say what changed.
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
                    @foreach ([
                        'Visits by gender' => $snapshot['attendance']['visits_by_gender'],
                        'Visits by grade' => $snapshot['attendance']['visits_by_grade'],
                        'Visits by school' => $snapshot['attendance']['visits_by_school'],
                        'Children by age band' => $snapshot['attendance']['unique_students_by_age_band'],
                    ] as $heading => $rows)
                        <div wire:key="breakdown-{{ Str::slug($heading) }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-2">{{ $heading }}</h3>
                            @if ($rows === [])
                                <p class="text-sm text-gray-500 dark:text-gray-400">No attendance in this period, so there is nothing to break down.</p>
                            @else
                                <table class="w-full text-sm">
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach ($rows as $label => $count)
                                            <tr wire:key="{{ Str::slug($heading) }}-{{ Str::slug($label) }}">
                                                <td class="py-1.5 text-gray-700 dark:text-gray-300">{{ $label }}</td>
                                                <td class="py-1.5 text-right font-medium text-gray-900 dark:text-white">{{ number_format($count) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-2">How regularly children attended</h3>
                    @if ($snapshot['attendance']['consistency_percent']['median'] === null)
                        <p class="text-sm text-gray-500 dark:text-gray-400">No attendance in this period.</p>
                    @else
                        <dl class="grid grid-cols-3 gap-4 text-sm">
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Median</dt>
                                <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $snapshot['attendance']['consistency_percent']['median'] }}%</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Attended 80%+ of weekdays</dt>
                                <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $snapshot['attendance']['consistency_percent']['students_at_or_above_80'] }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500 dark:text-gray-400">Attended under 40%</dt>
                                <dd class="text-lg font-semibold text-gray-900 dark:text-white">{{ $snapshot['attendance']['consistency_percent']['students_below_40'] }}</dd>
                            </div>
                        </dl>
                    @endif
                </div>

                @isset($snapshot['enrollment']['unavailable'])
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6 text-sm text-amber-800 dark:bg-amber-900/30 dark:border-amber-800 dark:text-amber-200">
                        <span class="font-medium">Enrollment for this period cannot be reconstructed.</span>
                        {{ $snapshot['enrollment']['unavailable'] }}
                    </div>
                @endisset

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-white mb-2">Read these figures with</h3>
                    <ul class="list-disc list-inside space-y-1 text-sm text-gray-600 dark:text-gray-300">
                        @foreach ($snapshot['caveats'] as $caveat)
                            <li wire:key="caveat-{{ $loop->index }}">{{ $caveat }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="no-print space-y-4">
            <details class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <summary class="p-4 text-sm font-semibold text-gray-700 dark:text-white cursor-pointer">
                    The exact payload ({{ number_format($payloadBytes) }} bytes, roughly {{ number_format((int) ceil($payloadBytes / 4)) }} tokens)
                </summary>
                <pre class="px-4 pb-4 overflow-x-auto text-xs text-gray-700 dark:text-gray-300">{{ $payloadJson }}</pre>
            </details>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 dark:bg-gray-800 dark:border-gray-700">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-white">Writing the narrative is not connected yet</h3>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                            Three things are still open: which provider to use, where its key lives, and whether
                            the request runs in a queue worker or makes someone wait. Until those are settled this
                            page shows the input and nothing is sent anywhere.
                        </p>
                    </div>
                    <button type="button" disabled
                        aria-describedby="generate-disabled-reason"
                        class="inline-flex items-center p-2 px-4 bg-primary-600 text-white rounded-lg text-sm opacity-50 cursor-not-allowed">
                        Generate narrative
                    </button>
                    <span id="generate-disabled-reason" class="sr-only">Disabled until a provider is configured.</span>
                </div>
            </div>
        </div>
    @endif
</div>
