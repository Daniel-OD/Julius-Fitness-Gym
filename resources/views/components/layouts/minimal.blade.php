@props([
    'title' => null,
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">

<head>
    <x-layouts.partials.head-meta :title="$title" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body {{ $attributes->merge(['class' => 'jf-min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased dark:bg-black dark:text-white']) }}>
    <div class="jf-safe-x jf-safe-b mx-auto flex min-h-full max-w-lg flex-col px-4 py-8 sm:px-8 sm:py-10">
        <div class="mb-6 flex items-center justify-between gap-4 sm:mb-8">
            <a href="{{ url('/') }}"
                class="text-sm font-medium text-zinc-500 transition-colors hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-white">
                Julius Fitness
            </a>
            <button type="button" data-theme-toggle
                class="rounded-full border border-zinc-200 px-3 py-1.5 text-xs font-medium text-zinc-600 transition-colors hover:bg-zinc-100 dark:border-white/15 dark:text-zinc-300 dark:hover:bg-white/5"
                aria-label="{{ __('app.ui.toggle_theme') }}">
                <span class="hidden dark:inline">{{ __('app.ui.light_mode') }}</span>
                <span class="dark:hidden">{{ __('app.ui.dark_mode') }}</span>
            </button>
        </div>

        {{ $slot }}
    </div>
</body>

</html>
