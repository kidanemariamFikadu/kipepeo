<div>
    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
        <!-- Modal header -->
        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                User History
            </h3>
            <button type="button" wire:click="closeModal"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white"
                data-modal-toggle="crud-modal">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
        </div>
        <!-- Modal body -->
        <div class="p-4 md:p">
            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                Changed by
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Original value
                            </th>
                            <th scope="col" class="px-6 py-3">
                                New value
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Changed type
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Changed at
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($userAudit as $audit)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                            <th scope="row"
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{App\Models\User::find($audit->user_id)->name}}
                            </th>
                            <td class="px-6 py-4 break-words">
                                {{json_encode($audit->old_values)}}
                            </td>
                            <td class="px-6 py-4 break-words">
                                {{json_encode($audit->new_values)}}
                            </td>
                            <td class="px-6 py-4">
                                {{$audit->event}}
                            </td>
                            <td class="px-6 py-4">
                                {{$audit->created_at}}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

