<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Julius Fitness Gym') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-canvas font-sans text-white antialiased">
    <div class="min-h-screen lg:flex">
        <div data-sidebar-backdrop
            class="fixed inset-0 z-30 hidden bg-black/70 backdrop-blur-sm lg:hidden"></div>

        <aside data-sidebar
            class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col border-r border-white/8 bg-canvas transition-transform duration-300 lg:static lg:translate-x-0">
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
            </main>
        </div>
    </div>
</body>

</html>
