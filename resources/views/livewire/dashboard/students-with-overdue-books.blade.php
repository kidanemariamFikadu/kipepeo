<div class="bg-white dark:bg-gray-800 p-6 rounded-md shadow-md">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-gray-700 dark:text-white text-xl font-semibold">Students with overdue books</h2>

        @if ($totalBooks > 0)
            <x-badge tone="warning" soft>
                {{ $totalBooks }} {{ Str::plural('book', $totalBooks) }}
            </x-badge>
        @endif
    </div>

    @if ($students->isEmpty())
        <p class="text-gray-500 dark:text-gray-400 text-sm">Nothing overdue — every book is back or still within its due date.</p>
    @else
        <ul class="divide-y divide-gray-100 dark:divide-gray-700">
            @foreach ($students as $student)
                <li wire:key="overdue-student-{{ $student['studentId'] }}" class="flex items-start gap-3 py-2.5 first:pt-0">
                    <x-avatar :name="$student['studentName']" size="md" tone="warning" />

                    <div class="min-w-0 flex-1">
                        <a href="{{ route('student-detail', $student['studentId']) }}"
                            class="block truncate text-sm font-medium text-gray-700 hover:text-primary-700 hover:underline focus:outline-none focus:ring-2 focus:ring-primary-500 rounded dark:text-white dark:hover:text-primary-400">
                            {{ $student['studentName'] }}
                        </a>

                        <ul class="mt-1 space-y-1">
                            @foreach ($student['books'] as $book)
                                <li wire:key="overdue-rental-{{ $book['rentalId'] }}" class="flex items-center gap-2">
                                    <span class="min-w-0 flex-1 truncate text-xs text-gray-500 dark:text-gray-400"
                                        title="{{ $book['title'] }}">
                                        {{ $book['title'] }}
                                    </span>

                                    <x-badge tone="danger" size="sm" soft class="shrink-0">
                                        {{ $book['daysOverdue'] }}d late
                                    </x-badge>

                                    {{-- Opens the same Return Book modal used on the book and
                                         student pages. It shows the title, borrower and dates
                                         with an explicit Return button, so an accidental click
                                         here cannot return a book on its own. --}}
                                    <button type="button"
                                        title="Return &quot;{{ $book['title'] }}&quot;"
                                        wire:click="$dispatch('openModal', { component: 'book.return-book', arguments: { rentalId: {{ $book['rentalId'] }} }})"
                                        class="shrink-0 rounded-md px-2 py-0.5 text-xs font-medium text-primary-700 hover:bg-primary-50 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:text-primary-300 dark:hover:bg-gray-700">
                                        Return
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    @if ($totalStudents > $students->count())
        <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
            Showing the {{ $students->count() }} longest overdue of {{ $totalStudents }} students &mdash;
            <a href="/books?tab=loan" class="underline hover:text-primary-700 dark:hover:text-primary-400">see all books on loan</a>.
        </p>
    @endif
</div>
