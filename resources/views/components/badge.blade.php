@props([
    'tone' => 'neutral',
    'size' => 'md',
    'soft' => false,
])

@php
    // One definition for the status pills. The app already used colour
    // meaning consistently -- red bad, amber warning, green good, primary
    // informational, grey inactive -- but the class string was retyped at
    // every site and had drifted on font weight and padding.
    $tones = [
        'primary' => 'bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-200',
        'success' => 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-200',
        'danger' => 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-200',
        'warning' => 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-200',
        'neutral' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
    ];

    // The dashboard cards use a lighter tint, so a column of badges does not
    // outshout the figures beside it. Kept as an opt-in rather than folded
    // into the tones above, which would have restyled those cards.
    $softTones = [
        'primary' => 'bg-primary-50 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300',
        'success' => 'bg-green-50 text-green-700 dark:bg-green-900/40 dark:text-green-300',
        'danger' => 'bg-red-50 text-red-700 dark:bg-red-900/40 dark:text-red-300',
        'warning' => 'bg-amber-50 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
        'neutral' => 'bg-gray-50 text-gray-600 dark:bg-gray-700/40 dark:text-gray-300',
    ];

    $palette = $soft ? $softTones : $tones;
    $sizes = ['sm' => 'px-2 py-0.5', 'md' => 'px-2.5 py-0.5'];
@endphp

<span {{ $attributes->class([
    'inline-flex items-center whitespace-nowrap rounded-full text-xs font-semibold',
    $sizes[$size] ?? $sizes['md'],
    $palette[$tone] ?? $palette['neutral'],
]) }}>{{ $slot }}</span>
