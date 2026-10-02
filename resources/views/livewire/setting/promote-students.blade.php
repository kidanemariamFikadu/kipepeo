<div>
    <x-flash-toast />

    <div class="p-2 md:p-6">
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-white">Promote / Graduate Students</h2>
                <a href="{{ route('settings') }}" class="text-sm text-primary-700 dark:text-primary-300 hover:underline">
                    &larr; Back to settings
                </a>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                Select the grades you want to move up. Every student currently in a selected grade will be moved to
                that grade's configured "next grade", and their prior grade record is kept for history. Grades with
                no next grade configured (final grades) will instead have their students marked as graduated and
                removed from the active roster — configure a next grade under Settings &rsaquo; Grades if a grade
                should promote instead of graduate.
            </p>

            @if ($this->gradeSummary->isEmpty())
                <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg p-6 text-gray-500 dark:text-gray-400">
                    No grades currently have students assigned.
                </div>
            @else
                @php
                    $impact = $this->selectionImpact;
                    $confirmMessage = $impact['graduating'] > 0
                        ? "Promote {$impact['promoting']} student(s) and graduate {$impact['graduating']} student(s)?\n\nGraduated students are removed from the active roster. This cannot be undone."
                        : "Promote {$impact['promoting']} student(s) to their next grade?\n\nThis cannot be undone.";
                @endphp

                {{-- wire:confirm must sit on the element carrying the action. On the
                     submit button it is never read, and the promotion fires on a
                     single click. --}}
                <form wire:submit="promote" wire:confirm="{{ $confirmMessage }}">
                    <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 w-10">
                                            <input aria-label="Select all grades" type="checkbox" wire:click="toggleSelectAll($event.target.checked)"
                                                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                        </th>
                                        <th scope="col" class="px-4 py-3">Current grade</th>
                                        <th scope="col" class="px-4 py-3">Students</th>
                                        <th scope="col" class="px-4 py-3">Promotes to / Graduates</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($this->gradeSummary as $grade)
                                        <tr wire:key="{{ $grade->id }}" class="border-b dark:border-gray-700">
                                            <td class="px-4 py-3">
                                                {{-- .live so the selected count and the confirmation
                                                     message reflect what is actually ticked. --}}
                                                <input type="checkbox" value="{{ $grade->id }}"
                                                    wire:model.live="selectedGrades"
                                                    aria-label="Select {{ $grade->grade }}"
                                                    class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                            </td>
                                            <th scope="row" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                {{ $grade->grade }}
                                            </th>
                                            <td class="px-4 py-3">
                                                <span class="inline-flex items-center justify-center rounded-full bg-primary-100 px-2.5 py-0.5 text-xs font-semibold text-primary-700 dark:bg-primary-900 dark:text-primary-200">
                                                    {{ $grade->current_students_count }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3">
                                                @if ($grade->nextGrade)
                                                    {{ $grade->nextGrade->grade }}
                                                @else
                                                    <span class="inline-flex items-center rounded-full bg-primary-100 px-2.5 py-0.5 text-xs font-semibold text-primary-700 dark:bg-primary-900 dark:text-primary-200">
                                                        Graduates
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="p-4 flex items-center justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ count($selectedGrades) }} grade(s) selected
                            </span>
                            <button type="submit" wire:loading.attr="disabled" wire:target="promote"
                                class="text-white inline-flex items-center bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 disabled:opacity-50">
                                <x-spinner class="h-4 w-4 mr-2 text-white" wire:loading wire:target="promote" />
                                Promote / graduate selected grades
                            </button>
                        </div>
                    </div>
                    @error('selectedGrades')
                        <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
                    @enderror
                </form>
            @endif
        </div>
    </div>
</div>
