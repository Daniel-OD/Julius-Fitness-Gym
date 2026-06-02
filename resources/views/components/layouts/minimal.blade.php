@props([
    'title' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? config('app.name', 'Julius Fitness Gym') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script>
        (function () {
            const stored = localStorage.getItem('theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = stored === 'dark' || (stored !== 'light' && prefersDark);
            document.documentElement.classList.toggle('dark', isDark);
        })();
    </script>

    @stack('head')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body {{ $attributes->merge(['class' => 'min-h-full bg-white font-sans text-zinc-900 antialiased dark:bg-black dark:text-white']) }}>
    <div class="mx-auto flex min-h-full max-w-lg flex-col px-6 py-10 sm:px-8">
        <div class="mb-8 flex items-center justify-between gap-4">
            <a href="{{ url('/') }}"
                class="text-sm font-medium text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                Julius Fitness
            </a>
            <button type="button" data-theme-toggle
                class="rounded-full border border-zinc-200 px-3 py-1.5 text-xs font-medium text-zinc-600 transition-colors hover:bg-zinc-50 dark:border-white/15 dark:text-zinc-300 dark:hover:bg-white/5"
                aria-label="{{ __('app.ui.toggle_theme') }}">
                <span class="hidden dark:inline">{{ __('app.ui.light_mode') }}</span>
                <span class="dark:hidden">{{ __('app.ui.dark_mode') }}</span>
            </button>
        </div>

        {{ $slot }}
    </div>

    <script>
        document.querySelector('[data-theme-toggle]')?.addEventListener('click', () => {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });
    </script>
</body>

</html>
