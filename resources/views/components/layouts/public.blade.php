<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? config('app.name', 'Julius Fitness Gym') }}</title>
    <meta name="description"
        content="{{ $description ?? 'Julius Fitness Gym — forță, condiționare și clase de grup. Alătură-te azi.' }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script>
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark');
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-black font-sans text-white antialiased">
    <header
        class="fixed inset-x-0 top-0 z-50 border-b border-white/0 bg-transparent transition-[background-color,border-color,backdrop-filter] duration-300"
        data-public-header>
        <div class="mx-auto flex h-16 max-w-7xl items-center gap-4 px-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                <span
                    class="flex h-9 w-9 items-center justify-center rounded-full border border-white/10 bg-white/5 text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6.5 6.5 17.5 17.5" /><path d="m21 21-1-1" /><path d="m3 3 1 1" />
                        <path d="m18 22 4-4" /><path d="m2 6 4-4" /><path d="m3 10 7-7" /><path d="m14 21 7-7" />
                    </svg>
                </span>
                <span class="text-sm font-semibold tracking-tight text-white">Julius Fitness</span>
            </a>

            <nav class="ml-6 hidden items-center gap-1 md:flex">
                <a href="#servicii"
                    class="rounded-full px-3 py-2 text-sm font-medium text-white/60 transition-colors duration-200 hover:bg-white/5 hover:text-white">Servicii</a>
                <a href="#program"
                    class="rounded-full px-3 py-2 text-sm font-medium text-white/60 transition-colors duration-200 hover:bg-white/5 hover:text-white">Program</a>
                <a href="#abonamente"
                    class="rounded-full px-3 py-2 text-sm font-medium text-white/60 transition-colors duration-200 hover:bg-white/5 hover:text-white">Abonamente</a>
                <a href="#contact"
                    class="rounded-full px-3 py-2 text-sm font-medium text-white/60 transition-colors duration-200 hover:bg-white/5 hover:text-white">Contact</a>
            </nav>

            <div class="ml-auto flex items-center gap-2">
                <x-ui.button :href="Route::has('login') ? route('login') : '#'" variant="ghost" size="md"
                    class="hidden sm:inline-flex">
                    Autentificare
                </x-ui.button>
                <x-ui.button href="#abonamente" variant="primary" size="md">Abonament</x-ui.button>
            </div>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="border-t border-white/8 bg-canvas">
        <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-10 md:grid-cols-4">
                <div class="col-span-2 md:col-span-1">
                    <div class="flex items-center gap-2.5">
                        <span
                            class="flex h-8 w-8 items-center justify-center rounded-full border border-white/10 bg-white/5">
                            <svg class="h-4 w-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M6.5 6.5 17.5 17.5" /><path d="m21 21-1-1" /><path d="m3 3 1 1" />
                                <path d="m18 22 4-4" /><path d="m2 6 4-4" /><path d="m3 10 7-7" /><path d="m14 21 7-7" />
                            </svg>
                        </span>
                        <span class="font-semibold tracking-tight">Julius Fitness</span>
                    </div>
                    <p class="mt-4 max-w-xs text-sm leading-relaxed text-white/45">
                        Antrenează inteligent. Rămâi constant. Obține rezultate.
                    </p>
                </div>
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-white/35">Sală</h4>
                    <ul class="mt-4 space-y-2 text-sm text-white/55">
                        <li><a href="#servicii" class="transition-colors hover:text-white">Servicii</a></li>
                        <li><a href="#program" class="transition-colors hover:text-white">Program</a></li>
                        <li><a href="#abonamente" class="transition-colors hover:text-white">Abonamente</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-white/35">Suport</h4>
                    <ul class="mt-4 space-y-2 text-sm text-white/55">
                        <li><a href="#contact" class="transition-colors hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-white/35">Program</h4>
                    <ul class="mt-4 space-y-2 text-sm text-white/55">
                        <li>Lun–Vin: 06:00 – 23:00</li>
                        <li>Sâm: 08:00 – 20:00</li>
                        <li>Dum: 08:00 – 18:00</li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 border-t border-white/8 pt-8 text-sm text-white/30">
                &copy; {{ date('Y') }} Julius Fitness Gym
            </div>
        </div>
    </footer>

    <script>
        const header = document.querySelector('[data-public-header]');
        const onScroll = () => {
            if (!header) return;
            if (window.scrollY > 24) {
                header.classList.add('border-white/8', 'bg-black/75', 'backdrop-blur-xl');
            } else {
                header.classList.remove('border-white/8', 'bg-black/75', 'backdrop-blur-xl');
            }
        };
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    </script>
</body>

</html>
