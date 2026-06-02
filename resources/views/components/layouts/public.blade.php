<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <x-layouts.partials.head-meta
        :title="$title ?? config('app.name', 'Julius Fitness Gym')"
        :description="$description ?? 'Julius Fitness Gym — forță, condiționare și clase de grup. Alătură-te azi.'" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-black font-sans text-white antialiased dark:bg-black">
    <header
        class="fixed inset-x-0 top-0 z-50 border-b border-transparent bg-transparent transition-[background-color,border-color,backdrop-filter] duration-300"
        data-public-header>
        <div class="mx-auto flex h-14 max-w-7xl items-center gap-3 px-4 sm:h-16 sm:gap-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-2.5">
                <span
                    class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full border border-white/10 bg-white/5 text-white">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <path d="M6.5 6.5 17.5 17.5" /><path d="m21 21-1-1" /><path d="m3 3 1 1" />
                        <path d="m18 22 4-4" /><path d="m2 6 4-4" /><path d="m3 10 7-7" /><path d="m14 21 7-7" />
                    </svg>
                </span>
                <span class="truncate text-sm font-semibold tracking-tight text-white">Julius Fitness</span>
            </a>

            <nav class="ml-2 hidden items-center gap-1 md:flex">
                <a href="#servicii"
                    class="rounded-full px-3 py-2 text-sm font-medium text-white/60 transition-colors duration-200 hover:bg-white/5 hover:text-white">Servicii</a>
                <a href="#program"
                    class="rounded-full px-3 py-2 text-sm font-medium text-white/60 transition-colors duration-200 hover:bg-white/5 hover:text-white">Program</a>
                <a href="#abonamente"
                    class="rounded-full px-3 py-2 text-sm font-medium text-white/60 transition-colors duration-200 hover:bg-white/5 hover:text-white">Abonamente</a>
                <a href="#contact"
                    class="rounded-full px-3 py-2 text-sm font-medium text-white/60 transition-colors duration-200 hover:bg-white/5 hover:text-white">Contact</a>
            </nav>

            <div class="ml-auto flex items-center gap-1 sm:gap-2">
                <button type="button" data-theme-toggle
                    class="rounded-full border border-white/15 px-3 py-1.5 text-xs font-medium text-white/70 transition-colors hover:bg-white/5"
                    aria-label="{{ __('app.ui.toggle_theme') }}">
                    <span class="hidden dark:inline">{{ __('app.ui.light_mode') }}</span>
                    <span class="dark:hidden">{{ __('app.ui.dark_mode') }}</span>
                </button>
                <x-ui.button :href="Route::has('login') ? route('login') : '#'" variant="ghost" size="md"
                    class="hidden sm:inline-flex">
                    Autentificare
                </x-ui.button>
                <x-ui.button href="#abonamente" variant="primary" size="md" class="text-xs sm:text-sm">Abonament</x-ui.button>
            </div>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="border-t border-white/8 bg-canvas">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:py-16 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-8 sm:gap-10 md:grid-cols-4">
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
            <div class="mt-10 border-t border-white/8 pt-8 text-sm text-white/30 sm:mt-12">
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
