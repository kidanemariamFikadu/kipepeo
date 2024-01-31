<div>
    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
        <!-- Modal header -->
        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Attendance History of {{ $attendance->student->name }}
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
                <div class="col-span-2 sm:col-span-1">
                    <label for="date"
                        class="block mb-2 text-sm font-medium text-gray-900 dark:text-white">Date</label>
                    <input type="date" id="date" wire:model="date"
                        onchange="window.livewire.dispatch('dateChanged', this.value);"
                        class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                        placeholder="date">
                    @error('date')
                        <span class="text-red-500 text-xs mt-3 block ">{{ $message }}</span>
                    @enderror
                </div>
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">
                                Time In
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Time out
                            </th>
                            <th scope="col" class="px-6 py-3">
                                Stayed hours
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($attendance->attrs as $attr)
                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                <th scope="row"
                                    class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $attr->time_in }}
                                </th>
                                <td class="px-6 py-4 break-words">
                                    {{ $attr->time_out }}
                                </td>
                                <td class="px-6 py-4 break-words">
                                    @if ($attr->time_out)
                                        @php
                                            $startDateTime = \Carbon\Carbon::createFromFormat('H:i:s', $attr->time_in);
                                            $endDateTime = \Carbon\Carbon::createFromFormat('H:i:s', $attr->time_out);

                                            $timeDifferenceInSeconds = $endDateTime->diffInSeconds($startDateTime);
                                        @endphp
                                        {{ \Carbon\Carbon::createFromTimestamp($timeDifferenceInSeconds)->format('H:i:s') }}
                                    @else
                                        @php
                                            $startDateTime = \Carbon\Carbon::createFromFormat('H:i:s', $attr->time_in);
                                            $endDateTime = \Carbon\Carbon::now();

                                            $timeDifferenceInSeconds = $endDateTime->diffInSeconds($startDateTime);
                                        @endphp
                                        {{ \Carbon\Carbon::createFromTimestamp($timeDifferenceInSeconds)->format('H:i:s') }}<span
                                            class="text-red-500">*</span>
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


<script>
    document.getElementById('date').addEventListener('change', function() {
        alert();
        @this.call('dateSelected', this.value);
    });
</script>
