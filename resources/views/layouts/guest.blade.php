<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Kipepeo | {{ config('app.name', 'Laravel') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
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
                    </div>
                </nav>
            </header>

            <main class="flex-1 flex items-center justify-center p-5">
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
                            <li><a href="https://kipepeosafespace.org" target="_blank" rel="noopener" class="hover:underline hover:text-primary-700 dark:hover:text-primary-400">kipepeosafespace.org</a></li>
                        </ul>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700 text-center text-xs text-gray-500 dark:text-gray-400">
                        © {{ date('Y') }} Kipepeo™. All Rights Reserved.
                    </div>
                </div>
            </footer>
        </div>

        <x-toast-container />

        @livewireScripts
    </body>
</html>
