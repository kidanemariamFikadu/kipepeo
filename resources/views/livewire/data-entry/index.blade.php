<div>
    <x-flash-toast />

    <div class="p-2 md:p-6">
        <h2 class="text-2xl font-semibold text-gray-700 dark:text-white mb-4">Data Entry</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div>
                @livewire('student.create-student',['isDataEntry' => true])
            </div>
            <div>
                @livewire('data-entry.add-student-attendance')
            </div>
        </div>
    </div>
</div>
