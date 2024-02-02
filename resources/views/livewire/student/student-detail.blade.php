<div>
    <a href="{{ route('students') }}"
    class="px-4 py-2 mb-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-e-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700 dark:bg-gray-700 dark:border-gray-600 dark:text-white dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-blue-500 dark:focus:text-white">
       < Back to Students
    </a>
    <div class="flex mb-4">
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700 w-1/2 p-4 mr-4">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Basic Information
                </h3>
            </div>
            <!-- Modal body -->
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
                            <option selected>Choose a gender</option>
                            <option value="female">Female</option>
                            <option value="male">Male</option>
                            <option value="other">Other</option>
                        </select>
                        @error('updateStudentForm.gender')
                            <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <label for="dob" class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date
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
                <button type="submit" wire:loading.attr="disabled"
                    class="text-white inline-flex items-center bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                    <svg class="h-5 w-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
                        <polyline points="17 21 17 13 7 13 7 21" />
                        <polyline points="7 3 7 8 15 8" />
                    </svg>
                    Save
                </button>
            </form>
        </div>

        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700 w-1/2 p-4">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Guardian Information
                </h3>
            </div>
            <!-- Modal body -->
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                <div
                    class="flex flex-column sm:flex-row flex-wrap space-y-4 sm:space-y-0 items-center justify-between pb-4">
                    <div>
                        <button
                            wire:click="$dispatch('openModal', { component: 'student.add-guardian' , arguments: { studentId: {{ $this->studentId }} }})"
                            class="px-3 py-1 bg-teal-500 text-white rounded mb-4 mt-2">+ Add Guardian</button>
                    </div>
                    <label for="table-search" class="sr-only">Search</label>
                    <div class="relative">
                        <div
                            class="absolute inset-y-0 left-0 rtl:inset-r-0 rtl:right-0 flex items-center ps-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" aria-hidden="true" fill="currentColor"
                                viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <input type="text" wire:model.live.debounce.500ms="search"
                            class="block p-2 ps-10 text-sm text-gray-900 border border-gray-300 rounded-lg w-80 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                            placeholder="Search for items">
                    </div>
                </div>
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                name
                            </th>
                            <th scope="col" class="px-6 py-3">
                                phone
                            </th>
                            <th scope="col" class="px-6 py-3">action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->studentDetails->guardians as $guardian)
                            <tr>
                                <td class="px-6 py-4 flex">
                                    {{ $guardian->guardian_name }}
                                    @if ($guardian->is_primary)
                                        <span class="me-2">
                                            <svg class="w-3.5 h-3.5 me-2 text-green-500 dark:text-green-400 flex-shrink-0"
                                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                            </svg>
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    {{ $guardian->guardian_phone }}
                                </td>
                                <td>
                                    @if (!$guardian->is_primary)
                                        <button wire:click="makeGuardianPrimary({{ $guardian->id }})"
                                            class="px-3 py-1 bg-teal-500 text-white rounded">
                                            Primary
                                        </button>
                                    @endif
                                    <button
                                        wire:click="$dispatch('openModal', { component: 'student.add-guardian', arguments: { studentGuardian: {{ $guardian->id }} } })"
                                        class="px-3 py-1 bg-teal-500 text-white rounded">
                                        Update
                                    </button>
                                    @if (!$guardian->is_primary)
                                        <button wire:click="deleteGuardian({{ $guardian->id }})"
                                            class="px-3 py-1 bg-red-500 text-white rounded">
                                            Remove
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="flex">
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700 w-1/2 p-4 mr-4">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    School Information
                </h3>
            </div>
            <!-- Modal body -->
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                <div
                    class="flex flex-column sm:flex-row flex-wrap space-y-4 sm:space-y-0 items-center justify-between pb-4">
                    <div>
                        <button
                            wire:click="$dispatch('openModal', { component: 'student.add-school' , arguments: { studentId: {{ $this->studentId }} }})"
                            class="px-3 py-1 bg-teal-500 text-white rounded mb-4 mt-2">+ Add School</button>
                    </div>
                </div>
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                name
                            </th>
                            <th scope="col" class="px-6 py-3">action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->studentDetails->schools as $school)
                            <tr>
                                <td class="px-6 py-4 flex">
                                    {{ $school->school->name }}
                                    @if ($school->is_current)
                                        <span class="me-2">
                                            <svg class="w-3.5 h-3.5 me-2 text-green-500 dark:text-green-400 flex-shrink-0"
                                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                            </svg>
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if (!$school->is_current)
                                        <button
                                            wire:click="makeSchoolPrimary({{ $school->student_id }},{{ $school->school_id }})"
                                            class="px-3 py-1 bg-teal-500 text-white rounded">
                                            Primary
                                        </button>

                                        <button
                                            wire:click="deleteSchool({{ $school->student_id }},{{ $school->school_id }})"
                                            class="px-3 py-1 bg-red-500 text-white rounded">
                                            Remove
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700 w-1/2 p-4">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    Grade Information
                </h3>
            </div>
            <!-- Modal body -->
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                <div
                    class="flex flex-column sm:flex-row flex-wrap space-y-4 sm:space-y-0 items-center justify-between pb-4">
                    <div>
                        <button
                            wire:click="$dispatch('openModal', { component: 'student.add-grade' , arguments: { studentId: {{ $this->studentId }} }})"
                            class="px-3 py-1 bg-teal-500 text-white rounded mb-4 mt-2">+ Add Grade</button>
                    </div>
                </div>
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                name
                            </th>
                            <th scope="col" class="px-6 py-3">action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($this->studentDetails->grades as $grade)
                            <tr>
                                <td class="px-6 py-4 flex">
                                    {{ $grade->gradeTable?->grade }}
                                    @if ($grade->is_current)
                                        <span class="me-2">
                                            <svg class="w-3.5 h-3.5 me-2 text-green-500 dark:text-green-400 flex-shrink-0"
                                                aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                                fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5Zm3.707 8.207-4 4a1 1 0 0 1-1.414 0l-2-2a1 1 0 0 1 1.414-1.414L9 10.586l3.293-3.293a1 1 0 0 1 1.414 1.414Z" />
                                            </svg>
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if (!$grade->is_current)
                                    <button
                                        wire:click="makeGradePrimary({{ $grade->student_id }},{{ $grade->grade }})"
                                        class="px-3 py-1 bg-teal-500 text-white rounded">
                                        Primary
                                    </button>

                                    <button
                                        wire:click="deleteGrade({{ $grade->student_id }},{{ $grade->grade }})"
                                        class="px-3 py-1 bg-red-500 text-white rounded">
                                        Remove
                                    </button>
                                @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
