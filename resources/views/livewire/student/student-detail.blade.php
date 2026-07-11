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
    <a href="{{ route('students') }}"
        class="inline-flex items-center gap-1 mb-4 text-sm text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back to Students
    </a>

    <div class="flex flex-wrap items-center gap-2 mb-4">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-white">{{ $studentDetails->name }}</h2>
        @if ($studentDetails->graduated_at)
            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-200">
                Graduated {{ $studentDetails->graduated_at->format('Y-m-d') }}
            </span>
        @elseif (auth()->user()->isAdmin() && $studentDetails->current_grade && ! $studentDetails->current_grade->next_grade_id)
            <button wire:click="graduate"
                wire:confirm="Graduate {{ $studentDetails->name }}? This marks them as an alumnus and removes them from the active roster."
                wire:loading.attr="disabled" wire:target="graduate"
                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-primary-600 text-white hover:bg-primary-700 disabled:opacity-50">
                <x-spinner class="h-3 w-3 mr-1 text-white" wire:loading wire:target="graduate" />
                Graduate student
            </button>
        @endif
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
                        <label for="name"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Name</label>
                        <input type="text" wire:model='updateStudentForm.name' id="name"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-600 focus:border-primary-600 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            placeholder="Jane Deo">
                        @error('updateStudentForm.name')
                            <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label for="gender"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Gender</label>
                        <select id="gender" wire:model='updateStudentForm.gender'
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                            <option value="" selected>Choose a gender</option>
                            <option value="female">Female</option>
                            <option value="male">Male</option>
                            <option value="other">Other</option>
                        </select>
                        @error('updateStudentForm.gender')
                            <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label for="dob"
                            class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date
                            of
                            Birth</label>
                        <input type="date" name="dob" id="dob" wire:model='updateStudentForm.dob'
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                            placeholder="Date of Birth">
                        @error('updateStudentForm.dob')
                            <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <button type="submit" wire:loading.attr="disabled" wire:target="update"
                    class="text-white inline-flex items-center bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800 disabled:opacity-50">
                    <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        wire:loading.remove wire:target="update">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                        <polyline points="17 21 17 13 7 13 7 21" />
                        <polyline points="7 3 7 8 15 8" />
                    </svg>
                    <x-spinner class="h-5 w-5 text-white" wire:loading wire:target="update" />
                    Save
                </button>
            </form>
        </div>

        <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 p-4">
            <div class="flex items-center justify-between p-4 md:p-5 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Guardian Information
                </h3>
                <button
                    wire:click="$dispatch('openModal', { component: 'student.add-guardian' , arguments: { studentId: {{ $this->studentId }} }})"
                    class="inline-flex items-center bg-primary-700 hover:bg-primary-800 text-white rounded-lg text-sm px-4 py-2">+ Add Guardian</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left rtl:text-right text-gray-700 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-4 py-3">Name</th>
                            <th scope="col" class="px-4 py-3">Phone</th>
                            <th scope="col" class="px-4 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->studentDetails->guardians as $guardian)
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                    <div class="flex items-center gap-2">
                                        {{ $guardian->guardian_name }}
                                        @if ($guardian->is_primary)
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200">
                                                Primary
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">{{ $guardian->guardian_phone }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        @if (!$guardian->is_primary)
                                            <button wire:click="makeGuardianPrimary({{ $guardian->id }})"
                                                wire:loading.attr="disabled" wire:loading.class="opacity-50"
                                                wire:target="makeGuardianPrimary({{ $guardian->id }})"
                                                class="text-xs text-primary-600 hover:underline dark:text-primary-300">
                                                Make primary
                                            </button>
                                        @endif
                                        <button
                                            wire:click="$dispatch('openModal', { component: 'student.add-guardian', arguments: { studentGuardian: {{ $guardian->id }} } })"
                                            class="text-xs text-teal-600 hover:underline dark:text-teal-300">
                                            Edit
                                        </button>
                                        @if (!$guardian->is_primary && auth()->user()->isAdmin())
                                            <button wire:click="deleteGuardian({{ $guardian->id }})"
                                                wire:confirm="Remove {{ $guardian->guardian_name }} as a guardian?"
                                                wire:loading.attr="disabled" wire:loading.class="opacity-50"
                                                wire:target="deleteGuardian({{ $guardian->id }})"
                                                class="text-xs text-red-600 hover:underline dark:text-red-300">
                                                Remove
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                    No guardians added yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 p-4">
            <div class="flex items-center justify-between p-4 md:p-5 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    School Information
                </h3>
                <button
                    wire:click="$dispatch('openModal', { component: 'student.add-school' , arguments: { studentId: {{ $this->studentId }} }})"
                    class="inline-flex items-center bg-primary-700 hover:bg-primary-800 text-white rounded-lg text-sm px-4 py-2">+ Add School</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left rtl:text-right text-gray-700 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-4 py-3">Name</th>
                            <th scope="col" class="px-4 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->studentDetails->schools as $school)
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                    <div class="flex items-center gap-2">
                                        {{ $school->school?->name ?? '(school removed)' }}
                                        @if ($school->is_current)
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200">
                                                Current
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if (!$school->is_current)
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                wire:click="makeSchoolPrimary({{ $school->student_id }},{{ $school->school_id }})"
                                                wire:loading.attr="disabled" wire:loading.class="opacity-50"
                                                wire:target="makeSchoolPrimary({{ $school->student_id }},{{ $school->school_id }})"
                                                class="text-xs text-primary-600 hover:underline dark:text-primary-300">
                                                Make current
                                            </button>
                                            @if (auth()->user()->isAdmin())
                                                <button
                                                    wire:click="deleteSchool({{ $school->student_id }},{{ $school->school_id }})"
                                                    wire:confirm="Remove this school record?"
                                                    wire:loading.attr="disabled" wire:loading.class="opacity-50"
                                                    wire:target="deleteSchool({{ $school->student_id }},{{ $school->school_id }})"
                                                    class="text-xs text-red-600 hover:underline dark:text-red-300">
                                                    Remove
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                    No schools added yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 p-4">
            <div class="flex items-center justify-between p-4 md:p-5 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Grade Information
                </h3>
                <button
                    wire:click="$dispatch('openModal', { component: 'student.add-grade' , arguments: { studentId: {{ $this->studentId }} }})"
                    class="inline-flex items-center bg-primary-700 hover:bg-primary-800 text-white rounded-lg text-sm px-4 py-2">+ Add Grade</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left rtl:text-right text-gray-700 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-4 py-3">Name</th>
                            <th scope="col" class="px-4 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->studentDetails->grades as $grade)
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                    <div class="flex items-center gap-2">
                                        {{ $grade->gradeTable?->grade ?? '(grade removed)' }}
                                        @if ($grade->is_current)
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200">
                                                Current
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    @if (!$grade->is_current)
                                        <div class="flex items-center justify-end gap-2">
                                            <button
                                                wire:click="makeGradePrimary({{ $grade->student_id }},{{ $grade->grade }})"
                                                wire:loading.attr="disabled" wire:loading.class="opacity-50"
                                                wire:target="makeGradePrimary({{ $grade->student_id }},{{ $grade->grade }})"
                                                class="text-xs text-primary-600 hover:underline dark:text-primary-300">
                                                Make current
                                            </button>
                                            @if (auth()->user()->isAdmin())
                                                <button
                                                    wire:click="deleteGrade({{ $grade->student_id }},{{ $grade->grade }})"
                                                    wire:confirm="Remove this grade record?"
                                                    wire:loading.attr="disabled" wire:loading.class="opacity-50"
                                                    wire:target="deleteGrade({{ $grade->student_id }},{{ $grade->grade }})"
                                                    class="text-xs text-red-600 hover:underline dark:text-red-300">
                                                    Remove
                                                </button>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                    No grades added yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
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
                        @forelse ($this->studentDetails->attendances as $attendance)
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
                                <td class="px-4 py-3">{{ $studentDetails->secondsToHms($attendance->total_time) }}</td>
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
            @if ($this->studentDetails->attendances->count() >= 15)
                <p class="px-4 pt-2 text-xs text-gray-400 dark:text-gray-500">
                    Showing most recent 15 &mdash; see the <a href="{{ route('report') }}" class="underline hover:text-primary-600 dark:hover:text-primary-400">Attendance Analytics report</a> for full history.
                </p>
            @endif
        </div>

        <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 p-4">
            <div class="flex items-center justify-between p-4 md:p-5 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Book Rentals
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left rtl:text-right text-gray-700 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-4 py-3">Title</th>
                            <th scope="col" class="px-4 py-3">Due Date</th>
                            <th scope="col" class="px-4 py-3">Status</th>
                            <th scope="col" class="px-4 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->studentDetails->rentals as $rental)
                            @php
                                $returnedAt = $rental->returned_at ? \Carbon\Carbon::parse($rental->returned_at) : null;
                                $isLate = $returnedAt ? false : \Carbon\Carbon::parse($rental->due_at)->isPast();
                            @endphp
                            <tr wire:key="rental-{{ $rental->id }}" class="border-b dark:border-gray-700">
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                    {{ $rental->book?->title ?? '(book removed)' }}
                                </td>
                                <td class="px-4 py-3">{{ \Carbon\Carbon::parse($rental->due_at)->format('Y-m-d') }}</td>
                                <td class="px-4 py-3">
                                    @if ($returnedAt)
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200">
                                            Returned
                                        </span>
                                    @elseif ($isLate)
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200">
                                            Overdue
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-200">
                                            Borrowed
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if (! $returnedAt)
                                        <div class="flex items-center justify-end">
                                            <button title="Return this book"
                                                class="p-2 text-primary-600 hover:bg-primary-50 rounded-lg dark:text-primary-300 dark:hover:bg-gray-700"
                                                wire:click="$dispatch('openModal', { component: 'book.return-book', arguments: { rentalId: {{ $rental->id }} }})">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" />
                                                </svg>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                    No book rentals yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 mt-4">
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-800 p-4">
            <div class="flex items-center justify-between p-4 md:p-5 border-b dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Volunteer Activities
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left rtl:text-right text-gray-700 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-4 py-3">Date</th>
                            <th scope="col" class="px-4 py-3">Duty</th>
                            <th scope="col" class="px-4 py-3">Volunteer</th>
                            <th scope="col" class="px-4 py-3">Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->studentDetails->volunteerActivities as $activity)
                            <tr class="border-b dark:border-gray-700">
                                <td class="px-4 py-3">{{ \Carbon\Carbon::parse($activity->date)->format('Y-m-d') }}</td>
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">{{ $activity->activityType?->name ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $activity->volunteer?->name ?? '—' }}</td>
                                <td class="px-4 py-3">{{ $activity->notes ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                    No volunteer activities recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>
</div>
