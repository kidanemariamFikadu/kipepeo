<div>
    <x-flash-toast />

    <div class="p-2 md:p-6">
        <a href="{{ route('settings-schools') }}"
            class="inline-flex items-center gap-1 mb-4 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Schools
        </a>

        <div class="flex flex-wrap items-center gap-2 mb-4">
            <h2 class="text-2xl font-semibold text-gray-700 dark:text-white">{{ $schoolDetails->name }}</h2>
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-200">
                {{ $roster->count() }} {{ Str::plural('student', $roster->count()) }}
            </span>
            <button wire:click="$dispatch('openModal', { component: 'setting.school', arguments: { schoolId: {{ $schoolId }} }})"
                class="inline-flex items-center bg-primary-700 hover:bg-primary-800 text-white rounded-lg text-sm px-4 py-2">
                Edit
            </button>
        </div>

        <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 p-4">
            <div class="flex items-center justify-between p-4 md:p-5 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Students
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left rtl:text-right text-gray-700 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-4 py-3">Name</th>
                            <th scope="col" class="px-4 py-3">Grade</th>
                            <th scope="col" class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roster as $row)
                            <tr wire:key="roster-{{ $row->id }}" class="border-b dark:border-gray-700">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                    {{ $row->student?->name ?? '(student removed)' }}
                                </td>
                                <td class="px-4 py-3">{{ $row->student?->grades->first()?->gradeTable?->grade ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    @if ($row->is_current)
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200">
                                            Current
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                            Past
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                    No students recorded for this school yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
