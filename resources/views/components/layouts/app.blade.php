<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kipepeo | {{ $title ?? config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased bg-white dark:bg-gray-900">
    <div class="flex flex-col h-screen justify-between">
        <header class="no-print">
            <nav class="bg-white border-gray-200 px-4 lg:px-6 py-2.5 dark:bg-gray-800">
                <div class="flex flex-wrap justify-between items-center mx-auto max-w-screen-xl">
                    <a href="/" class="flex items-center">
                        <img src="{{ asset('images/kipepeo-logo-dark.jpg') }}"
                            class="mr-3 h-6 sm:h-9 block dark:hidden" alt="Kipepeo Logo" />
                        <img src="{{ asset('images/kipepeo-logo-white.png') }}"
                            class="mr-3 h-6 sm:h-9 hidden dark:block" alt="Kipepeo Logo" />
                    </a>
                    <div class="flex items-center lg:order-2">
                        <button id="dropdownNavbarLink" data-dropdown-toggle="dropdownNavbar"
                            class="flex items-center justify-between w-full py-2 px-3 text-gray-900 hover:bg-gray-100 md:hover:bg-transparent md:border-0 md:hover:text-primary-700 md:p-0 md:w-auto dark:text-white md:dark:hover:text-primary-400 dark:focus:text-white dark:hover:bg-gray-700 md:dark:hover:bg-transparent">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-semibold text-primary-700 dark:bg-primary-900 dark:text-primary-200">
                                {{ Str::of(Auth::user()->name)->explode(' ')->map(fn ($p) => Str::substr($p, 0, 1))->take(2)->implode('') }}
                            </span>
                            <span class="ml-2 hidden sm:inline">{{ Auth::user()->name }}</span>
                            <svg class="w-2.5 h-2.5 ms-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 10 6">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="m1 1 4 4 4-4" />
                            </svg>
                        </button>
                        <!-- Dropdown menu -->
                        <div id="dropdownNavbar"
                            class="z-10 hidden font-normal bg-white divide-y divide-gray-100 rounded-lg shadow w-48 dark:bg-gray-700 dark:divide-gray-600">
                            <div class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                <div class="font-medium truncate">{{ Auth::user()->name }}</div>
                                <div class="truncate text-xs text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</div>
                            </div>
                            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200"
                                aria-labelledby="dropdownLargeButton">
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
                        <button data-collapse-toggle="mobile-menu-2" type="button"
                            class="inline-flex items-center p-2 ml-1 text-sm text-gray-500 rounded-lg lg:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600"
                            aria-controls="mobile-menu-2" aria-expanded="false">
                            <span class="sr-only">Open main menu</span>
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <svg class="hidden w-6 h-6" fill="currentColor" viewBox="0 0 20 20"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                    <div class="hidden justify-between items-center w-full lg:flex lg:w-auto lg:order-1"
                        id="mobile-menu-2">
                        @php
                            // Ordered by how often a typical staff member actually uses each
                            // page: daily operations first (mark attendance, manage the
                            // roster), then periodic tasks (lending, backfilling records
                            // entered outside the normal flow, review), then admin-only setup.
                            $navLinks = [
                                ['href' => '/', 'label' => 'Home', 'active' => request()->is('/')],
                                ['href' => '/attendance', 'label' => 'Attendance', 'active' => request()->is('attendance*')],
                                ['href' => '/students', 'label' => 'Students', 'active' => request()->is('students*', 'student-detail*')],
                                ['href' => '/books', 'label' => 'Books', 'active' => request()->is('books*', 'book-detail*')],
                                ['href' => '/data-entry', 'label' => 'Data Entry', 'active' => request()->is('data-entry*')],
                                ['href' => '/report', 'label' => 'Report', 'active' => request()->is('report*')],
                            ];
                            $adminLinks = [];
                            if (Auth::user()->isAdmin()) {
                                $adminLinks[] = ['href' => '/users', 'label' => 'Users', 'active' => request()->is('users*')];
                                $adminLinks[] = ['href' => '/invitation', 'label' => 'Invitations', 'active' => request()->is('invitation*')];
                                $adminLinks[] = ['href' => '/settings', 'label' => 'Settings', 'active' => request()->is('settings*', 'promote-students*')];
                            }
                        @endphp
                        <ul class="flex flex-col mt-4 font-medium lg:flex-row lg:items-center lg:space-x-2 lg:mt-0">
                            @foreach ($navLinks as $link)
                                <li>
                                    <a href="{{ $link['href'] }}"
                                        @if ($link['active']) aria-current="page" @endif
                                        class="block py-2 px-3 rounded-lg lg:px-3
                                            {{ $link['active']
                                                ? 'bg-primary-700 text-white dark:bg-primary-600'
                                                : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700' }}">
                                        {{ $link['label'] }}
                                    </a>
                                </li>
                            @endforeach
                            @if ($adminLinks !== [])
                                <li class="my-2 border-t border-gray-100 dark:border-gray-700 lg:my-0 lg:ml-1 lg:h-6 lg:w-px lg:border-t-0 lg:border-l lg:border-gray-200 lg:dark:border-gray-600" aria-hidden="true"></li>
                                @foreach ($adminLinks as $link)
                                    <li>
                                        <a href="{{ $link['href'] }}"
                                            @if ($link['active']) aria-current="page" @endif
                                            class="block py-2 px-3 rounded-lg lg:px-3
                                                {{ $link['active']
                                                    ? 'bg-primary-700 text-white dark:bg-primary-600'
                                                    : 'text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-700' }}">
                                            {{ $link['label'] }}
                                        </a>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
            </nav>
        </header>

        <main class="mt-2 p-5 mb-auto">
            {{ $slot }}
        </main>

        <footer class="p-4 bg-white md:p-6 dark:bg-gray-800 no-print border-t border-gray-200 dark:border-gray-700">
            <div class="mx-auto max-w-screen-xl">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <a href="/" class="flex items-center shrink-0">
                        <img src="{{ asset('images/kipepeo-logo-dark.jpg') }}"
                            class="h-6 block dark:hidden" alt="Kipepeo Logo" />
                        <img src="{{ asset('images/kipepeo-logo-white.png') }}"
                            class="h-6 hidden dark:block" alt="Kipepeo Logo" />
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
        @livewire('wire-elements-modal')
    </div>
</body>

</html>
