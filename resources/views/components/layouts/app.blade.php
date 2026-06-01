<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Julius Fitness Gym') }}</title>

    {{-- Apply persisted theme before paint to avoid a flash of the wrong color scheme --}}
    <script>
        if (localStorage.getItem('theme') === 'dark' ||
            (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50 font-sans text-gray-900 antialiased">
    <div class="min-h-screen lg:flex">
        {{-- Mobile backdrop --}}
        <div data-sidebar-backdrop
            class="fixed inset-0 z-30 hidden bg-gray-900/50 lg:hidden"></div>

        {{-- Sidebar --}}
        <aside data-sidebar
            class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-gray-200 bg-white transition-transform duration-200 lg:static lg:translate-x-0">
            <x-app.sidebar />
        </aside>

        {{-- Main column --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <x-app.topbar />

            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @isset($header)
                    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        {{ $header }}
                    </div>
                @endisset

                {{ $slot }}
            </main>
        </div>
    </div>
</body>

</html>
