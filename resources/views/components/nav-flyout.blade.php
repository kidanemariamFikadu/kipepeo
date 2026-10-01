@props(['label', 'href', 'links', 'active' => false])

{{--
    The collapsed-sidebar counterpart to a grouped nav item.

    Shown precisely when the expanded group is hidden. The icon is a link to
    the group's first page; hovering (or tabbing into it) reveals the group's
    full list, so a collapsed sidebar still exposes every destination.

    The panel is position:fixed rather than absolute because <nav> scrolls
    vertically, and a scroll container clips on both axes -- an absolutely
    positioned flyout would be cut off at the rail's edge. Its top is read
    from the trigger on hover; left is the collapsed rail width (72px).
--}}
<li class="hidden" :class="{ 'lg:block': collapsed }"
    x-data="{ open: false, top: 0 }"
    @mouseenter="top = $el.getBoundingClientRect().top; open = true"
    @mouseleave="open = false"
    @focusin="top = $el.getBoundingClientRect().top; open = true"
    @focusout="open = false">

    <a href="{{ $href }}" title="{{ $label }}" @if ($active) aria-current="page" @endif
        class="flex items-center justify-center rounded-lg px-2.5 py-2
            {{ $active ? 'bg-primary-700 text-white' : 'text-gray-200 hover:bg-white/10' }}">
        <span class="flex h-[18px] w-[18px] shrink-0 items-center justify-center">{{ $slot }}</span>
        <span class="sr-only">{{ $label }}</span>
    </a>

    <div x-show="open" style="display: none;"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 -translate-x-1"
        x-transition:enter-end="opacity-100 translate-x-0"
        :style="`top: ${top}px`"
        class="fixed left-[76px] z-50 w-56 rounded-lg bg-[#0d1e33] p-2 shadow-xl ring-1 ring-white/10">

        <p class="px-2.5 pb-1 pt-0.5 text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ $label }}</p>

        <ul class="flex flex-col gap-0.5">
            @foreach ($links as $link)
                <li>
                    <a href="{{ $link['href'] }}" @if ($link['active']) aria-current="page" @endif
                        class="block rounded-lg px-2.5 py-1.5 text-sm
                            {{ $link['active'] ? 'bg-primary-700 font-semibold text-white' : 'text-gray-300 hover:bg-white/10 hover:text-white' }}">
                        {{ $link['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</li>
