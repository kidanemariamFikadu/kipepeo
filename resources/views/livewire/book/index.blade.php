@php
    $loanTabActive = request()->query('tab') === 'loan';
@endphp
<div class="p-2 md:p-6">
    <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
        @if ($loanTabActive)
            <livewire:book.book-on-rent />
        @else
            <livewire:book.book-list />
        @endif
    </div>
</div>
