@props(['disabled' => false])

{{-- The counterpart to x-input, which existed while every <select> in the
     app was hand-rolled -- and so went without the dark: variants, leaving
     the filter dropdowns bright white on a dark page. --}}
<select {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500']) !!}>
    {{ $slot }}
</select>
