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
        <div>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-2xl font-semibold text-gray-700 dark:text-white">Students</h2>
                <button wire:click="$dispatch('openModal', { component: 'student.create-student' })"
                    class="inline-flex items-center bg-primary-700 hover:bg-primary-800 text-white rounded-lg text-sm px-4 py-2">+ Add student</button>
            </div>

            <div class="bg-white dark:bg-gray-800 relative shadow-md sm:rounded-lg overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 p-4">
                    <div class="relative w-full max-w-xs">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400"
                                fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input wire:model.live.debounce.300ms="search" type="text"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full pl-10 p-2"
                            placeholder="Search students">
                    </div>

                    <div class="flex items-center gap-2">
                        <label class="text-sm font-medium text-gray-900 dark:text-gray-300">School</label>
                        <select wire:model.live="school"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block p-2.5">
                            <option value="">All schools</option>
                            @foreach ($this->schoolList as $school)
                                <option value="{{ $school->id }}">{{ $school->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if (auth()->user()->isAdmin() && count($selectedStudents) > 0)
                    <div class="flex items-center justify-between gap-3 px-4 py-2 bg-primary-50 dark:bg-primary-950 border-y border-primary-100 dark:border-primary-900">
                        <span class="text-sm font-medium text-primary-800 dark:text-primary-200">
                            {{ count($selectedStudents) }} selected
                        </span>
                        <div class="flex items-center gap-3">
                            <button wire:click="$set('selectedStudents', [])" wire:loading.attr="disabled"
                                class="text-sm text-gray-600 dark:text-gray-300 hover:underline">
                                Clear
                            </button>
                            <button wire:click="deleteSelected" wire:loading.attr="disabled" wire:target="deleteSelected"
                                wire:confirm="Delete {{ count($selectedStudents) }} selected student(s)? This can't be undone from here."
                                class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg">
                                <x-spinner class="h-4 w-4 mr-1.5 text-white" wire:loading wire:target="deleteSelected" />
                                Delete selected
                            </button>
                        </div>
                    </div>
                @endif

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left text-gray-700 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                            <tr>
                                @if (auth()->user()->isAdmin())
                                    <th scope="col" class="px-4 py-3 w-10">
                                        <input type="checkbox" wire:click="toggleSelectAll"
                                            class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                            {{ count($selectedStudents) === $students->count() && $students->count() > 0 ? 'checked' : '' }}>
                                    </th>
                                @endif
                                @include('livewire.includes.table-sortable-th', [
                                    'name' => 'name',
                                    'displayName' => 'Name',
                                ])
                                <th scope="col" class="px-4 py-3">Grade</th>
                                <th scope="col" class="px-4 py-3">School</th>
                                @include('livewire.includes.table-sortable-th', [
                                    'name' => 'gender',
                                    'displayName' => 'Gender',
                                ])
                                <th scope="col" class="px-4 py-3">Age</th>
                                <th scope="col" class="px-4 py-3">Guardian</th>
                                @include('livewire.includes.table-sortable-th', [
                                    'name' => 'created_at',
                                    'displayName' => 'Joined',
                                ])
                                <th scope="col" class="px-4 py-3">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($students as $student)
                                <tr wire:key="{{ $student->id }}" class="border-b dark:border-gray-700">
                                    @if (auth()->user()->isAdmin())
                                        <td class="px-4 py-3">
                                            <input type="checkbox" wire:model="selectedStudents"
                                                class="rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                                value="{{ $student->id }}">
                                        </td>
                                    @endif
                                    <th scope="row" class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        <div class="flex items-center gap-3">
                                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700 dark:bg-primary-900 dark:text-primary-200">
                                                {{ Str::of($student->name)->explode(' ')->map(fn ($p) => Str::substr($p, 0, 1))->take(2)->implode('') }}
                                            </span>
                                            {{ $student->name }}
                                        </div>
                                    </th>
                                    <td class="px-4 py-3">{{ $student->grades->first()?->gradeTable?->grade ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $student->schools->first()?->school?->name ?? '—' }}</td>
                                    <td class="px-4 py-3">{{ $student->gender }}</td>
                                    <td class="px-4 py-3">{{ $student->studentAge }}</td>
                                    <td class="px-4 py-3">
                                        @php $primaryGuardian = $student->guardians->firstWhere('is_primary', true) ?? $student->guardians->first(); @endphp
                                        @if ($primaryGuardian)
                                            <div class="flex items-center">
                                                <svg class="w-3.5 h-3.5 me-2 shrink-0 {{ $primaryGuardian->is_primary ? 'text-green-500 dark:text-green-400' : 'text-gray-400 dark:text-gray-500' }}"
                                                    aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                                </svg>
                                                <span>{{ $primaryGuardian->guardian_name }} - {{ $primaryGuardian->guardian_phone }}</span>
                                            </div>
                                            @if ($student->guardians->count() > 1)
                                                <span class="text-xs text-gray-400 dark:text-gray-500">+{{ $student->guardians->count() - 1 }} more</span>
                                            @endif
                                        @else
                                            <span class="text-gray-400 dark:text-gray-500">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $student->created_at->format('Y-m-d') }}</td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="student-detail/{{ $student->id }}" title="Show student details"
                                                class="p-2 text-teal-600 hover:bg-teal-50 rounded-lg dark:text-teal-300 dark:hover:bg-gray-700">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
                                                </svg>
                                            </a>
                                            @if (auth()->user()->isAdmin())
                                                <button title="Delete student" wire:click="deleteRecord({{ $student->id }})"
                                                    wire:confirm="Delete {{ $student->name }}? This can't be undone from here."
                                                    wire:loading.attr="disabled" wire:target="deleteRecord({{ $student->id }})"
                                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg dark:text-red-300 dark:hover:bg-gray-700">
                                                    <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                        width="24" height="24" fill="none" viewBox="0 0 24 24"
                                                        wire:loading.remove wire:target="deleteRecord({{ $student->id }})">
                                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M5 7h14m-9 3v8m4-8v8M10 3h4a1 1 0 0 1 1 1v3H9V4a1 1 0 0 1 1-1ZM6 7h12v13a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7Z" />
                                                    </svg>
                                                    <x-spinner class="h-5 w-5" wire:loading wire:target="deleteRecord({{ $student->id }})" />
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->isAdmin() ? 9 : 8 }}" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                        No students found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="py-4 px-3">
                    <div class="flex ">
                        <div class="flex space-x-4 items-center mb-3">
                            <label class="w-32 text-sm font-medium text-gray-900 dark:text-gray-300">Per Page</label>
                            <select wire:model.live='perPage'
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 ">
                                <option value="5">5</option>
                                <option value="7">7</option>
                                <option value="10">10</option>
                                <option value="20">20</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    {{ $students->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
