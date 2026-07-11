<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kipepeo | {{ $title ?? config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased bg-white dark:bg-gray-900">
    @php
        $navLinks = [
            ['href' => '/', 'label' => 'Home', 'active' => request()->is('/')],
            ['href' => '/attendance', 'label' => 'Attendance', 'active' => request()->is('attendance*')],
            ['href' => '/volunteers', 'label' => 'Volunteers', 'active' => request()->is('volunteers*')],
            ['href' => '/students', 'label' => 'Students', 'active' => request()->is('students*', 'student-detail*')],
        ];
        $booksActive = request()->is('books*', 'book-detail*');
        $bookLinks = [
            ['href' => '/books', 'label' => 'Book', 'active' => $booksActive && request('tab') !== 'loan'],
            ['href' => '/books?tab=loan', 'label' => 'Books on Loan', 'active' => $booksActive && request('tab') === 'loan'],
        ];
        $dataEntryLink = ['href' => '/data-entry', 'label' => 'Data Entry', 'active' => request()->is('data-entry*')];
        $reportLink = ['href' => '/report', 'label' => 'Report', 'active' => request()->is('report*')];
        $adminLinks = [];
        if (Auth::user()->isAdmin()) {
            $adminLinks[] = ['href' => '/users', 'label' => 'Users', 'active' => request()->is('users*')];
            $adminLinks[] = ['href' => '/settings', 'label' => 'Settings', 'active' => request()->is('settings*', 'promote-students*')];
        }
        $adminActive = collect($adminLinks)->contains('active', true);
    @endphp

    <div x-data="{ collapsed: false, mobileOpen: false }"
        x-init="collapsed = localStorage.getItem('kp-sidebar-collapsed') === '1'" class="flex h-screen overflow-hidden">

        <div x-show="mobileOpen" x-transition.opacity @click="mobileOpen = false"
            class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden" style="display: none;"></div>

        <aside
            class="no-print fixed inset-y-0 left-0 z-40 flex w-64 shrink-0 flex-col bg-[#0d1e33] transition-transform duration-200 ease-in-out lg:static lg:translate-x-0"
            :class="[mobileOpen ? 'translate-x-0' : '-translate-x-full', collapsed ? 'lg:w-[72px]' : 'lg:w-64']">
            <div class="flex items-center border-b border-white/10 p-4">
                <a href="/" class="flex shrink-0 items-center rounded-md bg-white p-1.5 shadow">
                    <img src="{{ asset('images/kipepeo-logo-dark.jpg') }}" class="h-6 w-auto" :class="{ 'lg:h-4': collapsed }" alt="Kipepeo" />
                </a>
            </div>

            <nav class="flex-1 overflow-y-auto px-2.5 py-3">
                <ul class="flex flex-col gap-0.5">
                    @foreach ($navLinks as $link)
                        <li>
                            <a href="{{ $link['href'] }}" @if ($link['active']) aria-current="page" @endif
                                class="flex items-center gap-3 rounded-lg px-2.5 py-2 text-sm font-medium
                                    {{ $link['active'] ? 'bg-primary-700 text-white' : 'text-gray-200 hover:bg-white/10' }}">
                                <span class="flex h-[18px] w-[18px] shrink-0 items-center justify-center">
                                    @switch($link['label'])
                                        @case('Home')
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-8 9 8"/><path d="M5 10v10h14V10"/></svg>
                                            @break
                                        @case('Attendance')
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
                                            @break
                                        @case('Volunteers')
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                                            @break
                                        @case('Students')
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                            @break
                                    @endswitch
                                </span>
                                <span class="truncate" :class="{ 'lg:hidden': collapsed }">{{ $link['label'] }}</span>
                            </a>
                        </li>
                    @endforeach

                    <li :class="{ 'lg:hidden': collapsed }">
                        <details class="group" @if ($booksActive) open @endif>
                            <summary
                                class="flex cursor-pointer list-none items-center gap-3 rounded-lg px-2.5 py-2 text-sm font-medium marker:content-none hover:bg-white/10
                                    {{ $booksActive ? 'text-white' : 'text-gray-200' }}">
                                <span class="flex h-[18px] w-[18px] shrink-0 items-center justify-center">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 19V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v13H7a2 2 0 0 0-2 2Zm0 0a2 2 0 0 0 2 2h12M9 3v14m7 0v4"/></svg>
                                </span>
                                <span class="flex-1 truncate">Books</span>
                                <svg class="h-3 w-3 shrink-0 text-gray-400 transition-transform group-open:rotate-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
                            </summary>
                            <ul class="mt-0.5 flex flex-col gap-0.5 pl-[30px]">
                                @foreach ($bookLinks as $link)
                                    <li>
                                        <a href="{{ $link['href'] }}" @if ($link['active']) aria-current="page" @endif
                                            class="block rounded-lg px-2.5 py-1.5 text-sm
                                                {{ $link['active'] ? 'font-semibold text-white' : 'text-gray-400 hover:bg-white/10 hover:text-gray-200' }}">
                                            {{ $link['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </details>
                    </li>

                    <li>
                        <a href="{{ $dataEntryLink['href'] }}" @if ($dataEntryLink['active']) aria-current="page" @endif
                            class="flex items-center gap-3 rounded-lg px-2.5 py-2 text-sm font-medium
                                {{ $dataEntryLink['active'] ? 'bg-primary-700 text-white' : 'text-gray-200 hover:bg-white/10' }}">
                            <span class="flex h-[18px] w-[18px] shrink-0 items-center justify-center">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 013 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            </span>
                            <span class="truncate" :class="{ 'lg:hidden': collapsed }">{{ $dataEntryLink['label'] }}</span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ $reportLink['href'] }}" @if ($reportLink['active']) aria-current="page" @endif
                            class="flex items-center gap-3 rounded-lg px-2.5 py-2 text-sm font-medium
                                {{ $reportLink['active'] ? 'bg-primary-700 text-white' : 'text-gray-200 hover:bg-white/10' }}">
                            <span class="flex h-[18px] w-[18px] shrink-0 items-center justify-center">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l4-6 4 3 5-8"/></svg>
                            </span>
                            <span class="truncate" :class="{ 'lg:hidden': collapsed }">{{ $reportLink['label'] }}</span>
                        </a>
                    </li>

                    @if ($adminLinks !== [])
                        <li class="px-2.5 pb-1.5 pt-3.5 text-[10px] font-bold uppercase tracking-wider text-gray-400"
                            :class="{ 'lg:hidden': collapsed }">Admin only</li>

                        <li :class="{ 'lg:hidden': collapsed }">
                            <details class="group" @if ($adminActive) open @endif>
                                <summary
                                    class="flex cursor-pointer list-none items-center gap-3 rounded-lg px-2.5 py-2 text-sm font-medium marker:content-none hover:bg-white/10
                                        {{ $adminActive ? 'text-white' : 'text-gray-200' }}">
                                    <span class="flex h-[18px] w-[18px] shrink-0 items-center justify-center">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"/></svg>
                                    </span>
                                    <span class="flex-1 truncate">Admin</span>
                                    <svg class="h-3 w-3 shrink-0 text-gray-400 transition-transform group-open:rotate-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
                                </summary>
                                <ul class="mt-0.5 flex flex-col gap-0.5 pl-[30px]">
                                    @foreach ($adminLinks as $link)
                                        <li>
                                            <a href="{{ $link['href'] }}" @if ($link['active']) aria-current="page" @endif
                                                class="block rounded-lg px-2.5 py-1.5 text-sm
                                                    {{ $link['active'] ? 'font-semibold text-white' : 'text-gray-400 hover:bg-white/10 hover:text-gray-200' }}">
                                                {{ $link['label'] }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </details>
                        </li>
                    @endif
                </ul>
            </nav>

            <button type="button"
                @click="collapsed = !collapsed; localStorage.setItem('kp-sidebar-collapsed', collapsed ? '1' : '0')"
                class="mx-2.5 mb-2 hidden items-center justify-center rounded-lg border border-white/10 py-1.5 text-gray-400 hover:bg-white/10 hover:text-gray-200 lg:flex"
                aria-label="Collapse sidebar">
                <svg class="h-3.5 w-3.5 transition-transform" :class="{ 'rotate-180': collapsed }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 17l-5-5 5-5"/><path d="M18 17l-5-5 5-5"/></svg>
            </button>

            <div class="relative border-t border-white/10 p-2.5">
                <button id="dropdownNavbarLink" data-dropdown-toggle="dropdownNavbar" data-dropdown-placement="top"
                    type="button" class="flex w-full items-center gap-2.5 rounded-lg p-1.5 text-left hover:bg-white/10">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700 dark:bg-primary-900 dark:text-primary-200">
                        {{ Str::of(Auth::user()->name)->explode(' ')->map(fn ($p) => Str::substr($p, 0, 1))->take(2)->implode('') }}
                    </span>
                    <span class="min-w-0 flex-1" :class="{ 'lg:hidden': collapsed }">
                        <span class="block truncate text-xs font-semibold text-white">{{ Auth::user()->name }}</span>
                        <span class="block truncate text-[11px] text-gray-400">{{ Auth::user()->email }}</span>
                    </span>
                </button>
                <!-- Dropdown menu -->
                <div id="dropdownNavbar"
                    class="z-10 hidden w-56 font-normal bg-white divide-y divide-gray-100 rounded-lg shadow dark:bg-gray-700 dark:divide-gray-600">
                    <div class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                        <div class="font-medium truncate">{{ Auth::user()->name }}</div>
                        <div class="truncate text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</div>
                    </div>
                    <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownLargeButton">
                        <li>
                            <a href="{{ route('my-profile') }}"
                                class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">My Profile</a>
                        </li>
                    </ul>
                    <div class="py-1">
                        <form method="POST" action="{{ route('logout') }}" x-data>
                            @csrf
                            <a href="{{ route('logout') }}" @click.prevent="$root.submit();"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 dark:text-gray-200 dark:hover:text-white">Sign
                                out</a>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col overflow-y-auto">
            <div class="no-print flex items-center justify-between border-b border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-800 lg:hidden">
                <a href="/" class="flex items-center rounded-md bg-white p-1.5 shadow">
                    <img src="{{ asset('images/kipepeo-logo-dark.jpg') }}" class="h-6 w-auto" alt="Kipepeo Logo" />
                </a>
                <button type="button" @click="mobileOpen = true"
                    class="inline-flex items-center rounded-lg p-2 text-sm text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
                    <span class="sr-only">Open main menu</span>
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd"
                            d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                </button>
            </div>

            <main class="mt-2 p-5 mb-auto">
                {{ $slot }}
            </main>

            <footer class="p-4 bg-white md:p-6 dark:bg-gray-800 no-print border-t border-gray-200 dark:border-gray-700">
                <div class="mx-auto max-w-screen-xl">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <a href="/" class="flex items-center shrink-0 rounded-md bg-white p-1.5 shadow">
                            <img src="{{ asset('images/kipepeo-logo-dark.jpg') }}" class="h-6 w-auto" alt="Kipepeo Logo" />
                        </a>
                        <ul class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-gray-600 dark:text-gray-300">
                            <li><a href="/" class="hover:underline hover:text-primary-700 dark:hover:text-primary-400">Home</a></li>
                            <li><a href="/students" class="hover:underline hover:text-primary-700 dark:hover:text-primary-400">Students</a></li>
                            <li><a href="/report" class="hover:underline hover:text-primary-700 dark:hover:text-primary-400">Reports</a></li>
                            <li><a href="/books" class="hover:underline hover:text-primary-700 dark:hover:text-primary-400">Books</a></li>
                            <li><a href="{{ route('my-profile') }}" class="hover:underline hover:text-primary-700 dark:hover:text-primary-400">My Profile</a></li>
                            <li><a href="https://kipepeosafespace.org" target="_blank" rel="noopener" class="hover:underline hover:text-primary-700 dark:hover:text-primary-400">kipepeosafespace.org</a></li>
                        </ul>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 text-center text-xs text-gray-500 dark:text-gray-400">
                        © {{ date('Y') }} Kipepeo™. All Rights Reserved.
                    </div>
                </div>
            </footer>
        </div>

        @livewire('wire-elements-modal')

        <x-toast-container />
    </div>
</body>

</html>
