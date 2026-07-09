@props(['title', 'subtitle' => null])
<div class="print-header mb-4">
    <h1 class="text-xl font-bold">Kipepeo — {{ $title }}</h1>
    @if ($subtitle)
        <p class="text-sm">{{ $subtitle }}</p>
    @endif
    <p class="text-xs text-gray-500">Generated {{ now()->format('Y-m-d H:i') }} by {{ auth()->user()?->name }}</p>
    <hr class="my-2 border-gray-400">
</div>
