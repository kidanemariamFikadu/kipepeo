@props([
    'name' => null,
    'size' => 'md',
    'tone' => 'primary',
])

@php
    // The initials circle beside a person's name. Ten copies of this, each
    // recomputing the initials inline -- and h-9 carried text-xs in two of
    // them and text-sm in the other two.
    $initials = $name
        ? Str::of($name)->explode(' ')->map(fn ($part) => Str::substr($part, 0, 1))->take(2)->implode('')
        : '';

    $sizes = [
        'sm' => 'h-8 w-8 text-xs',
        'md' => 'h-9 w-9 text-sm',
    ];

    $tones = [
        'primary' => 'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-200',
        'warning' => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200',
    ];
@endphp

{{-- The slot is for the in-session dot, which is positioned against this
     element -- so a caller that uses it also passes class="relative". --}}
<span {{ $attributes->class([
    'flex shrink-0 items-center justify-center rounded-full font-semibold',
    $sizes[$size] ?? $sizes['md'],
    $tones[$tone] ?? $tones['primary'],
]) }}>{{ $initials }}{{ $slot }}</span>
