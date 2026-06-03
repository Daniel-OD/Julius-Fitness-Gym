<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <x-layouts.partials.head-meta :title="$title ?? null" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased dark:bg-canvas dark:text-white">
    <div class="min-h-screen lg:flex">
        <div data-sidebar-backdrop
            class="fixed inset-0 z-30 hidden bg-black/50 backdrop-blur-sm lg:hidden dark:bg-black/70"></div>

        <aside data-sidebar
            class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-zinc-200 bg-white transition-transform duration-300 dark:border-white/8 dark:bg-canvas lg:static lg:translate-x-0">
            <x-app.sidebar />
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <x-app.topbar />

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @isset($header)
                    <div class="mb-8 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        {{ $header }}
                    </div>
                @endisset

                {{ $slot }}

                <x-studio.signature variant="inline" class="mt-10 opacity-60" />
            </main>
        </div>
    </div>
    <x-studio.html-comment />
</body>

</html>
