@props(['submit'])

<div {{ $attributes->merge(['class' => 'relative bg-white rounded-lg shadow dark:bg-gray-800 p-4 md:p-6']) }}>
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $description }}</p>

    <form wire:submit="{{ $submit }}" class="mt-6">
        <div class="grid grid-cols-6 gap-6">
            {{ $form }}
        </div>

        @if (isset($actions))
            <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                {{ $actions }}
            </div>
        @endif
    </form>
</div>
