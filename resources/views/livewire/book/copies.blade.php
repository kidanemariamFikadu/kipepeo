<div>
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                    Copy Number
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                    Status
                </th>
                <th scope="col"
                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">
                    Actions
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
            @forelse ($copies as $copy)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900 dark:text-white">{{ $copy->copy_number }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if ($copy->status == 'borrowed')
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-500 dark:text-red-100">
                                Borrowed
                            </span>
                        @elseif($copy->status == 'lost')
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-500 dark:text-yellow-100">
                                Lost
                            </span>
                        @elseif($copy->status == 'available')
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-500 dark:text-green-100">
                                Available
                            </span>
                        @elseif($copy->status == 'stolen')
                            <span
                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-500 dark:text-blue-100">
                                Stolen
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                        <a href="#" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap" colspan="3">
                        <div class="text-sm text-gray-500 dark:text-gray-300">No copies found</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {{ $copies->links()}}
</div>
