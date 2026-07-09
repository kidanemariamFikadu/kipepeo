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
