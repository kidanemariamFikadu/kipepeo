@props(['size' => 'md'])

@php
    // md is the modal/form submit; sm is the "+ Add X" in a list header and
    // the Apply buttons on the report filters. Both were already in use --
    // as two separately retyped class strings that had lost the focus ring
    // and the dark variants along the way.
    $sizes = [
        'md' => 'px-5 py-2.5',
        'sm' => 'px-4 py-2',
    ];
@endphp

<button {{ $attributes->merge(['type' => 'submit'])->class([
    'text-white inline-flex items-center justify-center bg-primary-700 hover:bg-primary-800',
    'focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm text-center',
    'dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800',
    'disabled:opacity-50 transition ease-in-out duration-150',
    $sizes[$size] ?? $sizes['md'],
]) }}>
    {{ $slot }}
</button>
