<div {{ $attributes->merge(['class' => 'relative bg-white rounded-lg shadow dark:bg-gray-800 p-4 md:p-6']) }}>
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>

    <div class="mt-6">
        {{ $content }}
    </div>
</div>
