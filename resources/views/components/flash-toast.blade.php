{{--
    Invisible dispatcher: Livewire components include this so that a session
    flash set during their render fires a browser event picked up by the
    global <x-toast-container /> in the layout. The toast itself can't live
    here — the wire-elements modal wrapper has `transform`/`overflow-hidden`,
    which traps and clips position:fixed children.
--}}
@if (session('success') || session('error'))
    @php
        $flashType = session('error') ? 'error' : 'success';
        $flashMessage = session('error') ?? session('success');
    @endphp
    <div wire:key="flash-toast-{{ uniqid('', true) }}" x-data
        x-init="$nextTick(() => $dispatch('flash-toast', { type: @js($flashType), message: @js($flashMessage) }))"
        style="display: none;"></div>
@endif
