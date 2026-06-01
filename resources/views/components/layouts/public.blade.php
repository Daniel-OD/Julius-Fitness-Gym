<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? config('app.name', 'Julius Fitness Gym') }}</title>
    <meta name="description"
        content="{{ $description ?? 'Julius Fitness Gym — strength, conditioning and group classes. Join today.' }}">

    <script>
        if (localStorage.getItem('theme') === 'dark' ||
            (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-white font-sans text-gray-900 antialiased dark:bg-gray-950 dark:text-gray-100">
    {{-- Header --}}
    <header class="sticky top-0 z-40 border-b border-gray-200/80 bg-white/80 backdrop-blur dark:border-gray-800/80 dark:bg-gray-950/80">
        <div class="mx-auto flex h-16 max-w-7xl items-center gap-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-600 text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6.5 6.5 17.5 17.5" /><path d="m21 21-1-1" /><path d="m3 3 1 1" />
                        <path d="m18 22 4-4" /><path d="m2 6 4-4" /><path d="m3 10 7-7" /><path d="m14 21 7-7" />
                    </svg>
                </span>
                <span class="text-lg font-bold tracking-tight">Julius Fitness</span>
            </a>

            <nav class="ml-6 hidden items-center gap-1 md:flex">
                <a href="#features" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white">Features</a>
                <a href="#plans" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white">Plans</a>
                <a href="#about" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white">About</a>
                <a href="#contact" class="rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 dark:hover:text-white">Contact</a>
            </nav>

            <div class="ml-auto flex items-center gap-2">
                <button type="button" data-theme-toggle title="Toggle theme"
                    class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-800">
                    <svg class="hidden h-5 w-5 dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="4" /><path d="M12 2v2" /><path d="M12 20v2" />
                        <path d="m4.93 4.93 1.41 1.41" /><path d="m17.66 17.66 1.41 1.41" /><path d="M2 12h2" />
                        <path d="M20 12h2" /><path d="m6.34 17.66-1.41 1.41" /><path d="m19.07 4.93-1.41 1.41" />
                    </svg>
                    <svg class="block h-5 w-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
                    </svg>
                </button>

                {{-- Login/portal links resolve once the backend adds auth routes. --}}
                <x-ui.button :href="Route::has('login') ? route('login') : '#'" variant="ghost" size="md"
                    class="hidden sm:inline-flex">
                    Log in
                </x-ui.button>
                <x-ui.button href="#plans" variant="primary" size="md">Join now</x-ui.button>
            </div>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="border-t border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-900">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-8 md:grid-cols-4">
                <div class="col-span-2 md:col-span-1">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand-600 text-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6.5 6.5 17.5 17.5" /><path d="m21 21-1-1" /><path d="m3 3 1 1" />
                                <path d="m18 22 4-4" /><path d="m2 6 4-4" /><path d="m3 10 7-7" /><path d="m14 21 7-7" />
                            </svg>
                        </span>
                        <span class="font-bold">Julius Fitness</span>
                    </div>
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">Train hard. Stay consistent. Get results.</p>
                </div>
                <div>
                    <h4 class="text-sm font-semibold">Gym</h4>
                    <ul class="mt-3 space-y-2 text-sm text-gray-500 dark:text-gray-400">
                        <li><a href="#features" class="hover:text-gray-900 dark:hover:text-white">Features</a></li>
                        <li><a href="#plans" class="hover:text-gray-900 dark:hover:text-white">Plans</a></li>
                        <li><a href="#about" class="hover:text-gray-900 dark:hover:text-white">About</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold">Support</h4>
                    <ul class="mt-3 space-y-2 text-sm text-gray-500 dark:text-gray-400">
                        <li><a href="#contact" class="hover:text-gray-900 dark:hover:text-white">Contact</a></li>
                        <li><a href="#" class="hover:text-gray-900 dark:hover:text-white">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-sm font-semibold">Hours</h4>
                    <ul class="mt-3 space-y-2 text-sm text-gray-500 dark:text-gray-400">
                        <li>Mon–Fri: 06:00 – 23:00</li>
                        <li>Sat–Sun: 08:00 – 20:00</li>
                    </ul>
                </div>
            </div>
            <div class="mt-10 border-t border-gray-200 pt-6 text-sm text-gray-400 dark:border-gray-800">
                &copy; {{ date('Y') }} Julius Fitness Gym. All rights reserved.
            </div>
        </div>
    </footer>
</body>

</html>
