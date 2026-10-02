<div>
    <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
        <!-- Modal header -->
        <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                User History
            </h3>
            <x-modal-close type="button" wire:click="closeModal"
                data-modal-toggle="crud-modal" />
        </div>
        <!-- Modal body -->
        <div class="p-4 md:p-5">
            @php
                // Allow-list rather than dumping the whole audit payload: it is
                // the last line of defence keeping credential columns off this
                // screen if one ever slips back into the recorded values.
                $auditFields = [
                    'name' => 'Name',
                    'email' => 'Email',
                    'role' => 'Role',
                    'job_title_id' => 'Job title',
                    'must_reset_password' => 'Must reset password',
                ];
            @endphp
            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left rtl:text-right text-gray-700 dark:text-gray-400">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-900 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-4 py-3">Changed by</th>
                            <th scope="col" class="px-4 py-3">Original value</th>
                            <th scope="col" class="px-4 py-3">New value</th>
                            <th scope="col" class="px-4 py-3">Change type</th>
                            <th scope="col" class="px-4 py-3">Changed at</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($userAudit as $audit)
                            <tr class="border-b dark:border-gray-700">
                                <th scope="row"
                                    class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                    {{ $audit->user?->name ?? 'Unknown user' }}
                                </th>
                                @foreach (['old_values', 'new_values'] as $valueSet)
                                    <td class="px-4 py-3 break-words">
                                        @forelse (array_intersect_key($audit->{$valueSet} ?? [], $auditFields) as $field => $value)
                                            <div>
                                                <span class="text-gray-500 dark:text-gray-400">{{ $auditFields[$field] }}:</span>
                                                {{ is_bool($value) ? ($value ? 'Yes' : 'No') : ($value ?? '—') }}
                                            </div>
                                        @empty
                                            <span class="text-gray-400 dark:text-gray-500">&mdash;</span>
                                        @endforelse
                                    </td>
                                @endforeach
                                <td class="px-4 py-3">
                                    <x-badge tone="primary">
                                        {{ Str::title($audit->event) }}
                                    </x-badge>
                                </td>
                                <td class="px-4 py-3">{{ $audit->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                    No history recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
