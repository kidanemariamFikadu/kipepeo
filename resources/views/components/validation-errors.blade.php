@if ($errors->any())
    <div {{ $attributes->merge(['class' => 'p-4 rounded-lg bg-red-50 dark:bg-gray-700']) }}>
        <div class="font-medium text-red-700 dark:text-red-400">{{ __('Whoops! Something went wrong.') }}</div>

        <ul class="mt-3 list-disc list-inside text-sm text-red-700 dark:text-red-400">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
