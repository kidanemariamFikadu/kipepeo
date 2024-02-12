<div>
    <h2 class="text-gray-700 dark:text-white text-xl font-semibold mb-4">Students stat by school</h2>
    <div class="max-w-sm p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
        <ul class="text-gray-700 dark:text-white">
            @foreach ($this->studentsBySchool as $school)
                <li class="flex">
                    {{ $school->name }} - {{ $school->students_count }}
                </li>
            @endforeach
        </ul>
    </div>
    {{ $this->studentsBySchool }}
</div>
