@php
    $membershipCta = auth('member')->check()
        ? route('member.plans')
        : (Route::has('member.register') ? route('member.register') : '#abonamente');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth dark">

<head>
    <x-layouts.partials.head-meta
        :title="$title ?? config('app.name', 'Julius Fitness Gym')"
        :description="$description ?? 'Julius Fitness Gym — forță, condiționare și clase de grup. Alătură-te azi.'" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="jf-min-h-screen bg-zinc-100 font-sans text-zinc-900 antialiased dark:bg-black dark:text-white">
    <header
        class="jf-safe-t fixed inset-x-0 top-0 z-50 border-b border-transparent bg-transparent transition-[background-color,border-color,backdrop-filter] duration-300"
        data-public-header>
        <div class="jf-safe-x mx-auto flex h-14 max-w-7xl items-center gap-2 px-4 sm:h-16 sm:gap-4 sm:px-6 lg:px-8">
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

            <button type="button" data-public-nav-toggle
                class="jf-touch-target ml-auto inline-flex items-center justify-center rounded-full border border-white/15 p-2 text-white md:hidden"
                aria-expanded="false" aria-controls="public-mobile-nav" aria-label="Menu">
                <svg class="h-5 w-5" data-public-nav-icon-open viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round">
                    <path d="M4 12h16" /><path d="M4 6h16" /><path d="M4 18h16" />
                </svg>
                <svg class="hidden h-5 w-5" data-public-nav-icon-close viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <path d="M18 6 6 18" /><path d="m6 6 12 12" />
                </svg>
            </button>

            <nav class="ml-2 hidden flex-1 items-center gap-1 md:flex">
                <a href="#servicii"
                    class="rounded-full px-3 py-2 text-sm font-medium text-white/60 transition-colors duration-200 hover:bg-white/5 hover:text-white">Servicii</a>
                <a href="#program"
                    class="rounded-full px-3 py-2 text-sm font-medium text-white/60 transition-colors duration-200 hover:bg-white/5 hover:text-white">Program</a>
                <a href="#abonamente"
                    class="rounded-full px-3 py-2 text-sm font-medium text-white/60 transition-colors duration-200 hover:bg-white/5 hover:text-white">Abonamente</a>
                <a href="#contact"
                    class="rounded-full px-3 py-2 text-sm font-medium text-white/60 transition-colors duration-200 hover:bg-white/5 hover:text-white">Contact</a>
            </nav>

            <div class="ml-auto hidden items-center gap-1 sm:gap-2 md:flex">
                <button type="button" data-theme-toggle
                    class="rounded-full border border-white/15 px-3 py-1.5 text-xs font-medium text-white/70 transition-colors hover:bg-white/5"
                    aria-label="{{ __('app.ui.toggle_theme') }}">
                    <span class="hidden dark:inline">{{ __('app.ui.light_mode') }}</span>
                    <span class="dark:hidden">{{ __('app.ui.dark_mode') }}</span>
                </button>
                <x-ui.button :href="Route::has('member.login') ? route('member.login') : '#'" variant="ghost" size="md"
                    class="hidden sm:inline-flex">
                    Autentificare
                </x-ui.button>
                @if (Route::has('member.register'))
                    <x-ui.button :href="route('member.register')" variant="ghost" size="md">
                        Înregistrare
                    </x-ui.button>
                @endif
                <x-ui.button :href="$membershipCta" variant="primary" size="md" class="text-xs sm:text-sm">Abonament</x-ui.button>
            </div>
        </div>

        <nav id="public-mobile-nav" data-public-nav-panel
            class="jf-mobile-nav-panel jf-safe-x border-t border-white/10 bg-black/90 backdrop-blur-xl md:hidden">
            <div class="flex flex-col gap-1 px-4 py-4">
                <a href="#servicii"
                    class="jf-touch-target rounded-xl px-4 py-3 text-base font-medium text-white/80">Servicii</a>
                <a href="#program"
                    class="jf-touch-target rounded-xl px-4 py-3 text-base font-medium text-white/80">Program</a>
                <a href="#abonamente"
                    class="jf-touch-target rounded-xl px-4 py-3 text-base font-medium text-white/80">Abonamente</a>
                <a href="#contact"
                    class="jf-touch-target rounded-xl px-4 py-3 text-base font-medium text-white/80">Contact</a>
                <div class="mt-2 flex flex-col gap-2 border-t border-white/10 pt-4">
                    <div x-data="{ open: false }" class="flex flex-col">
                        <button type="button" @click="open = !open"
                            class="jf-touch-target flex items-center justify-between rounded-xl px-4 py-3 text-base font-medium text-white/80">
                            Cont
                            <svg class="h-4 w-4 transition-transform duration-200" :class="open && 'rotate-180'"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>
                        <div x-show="open" class="flex flex-col gap-1 px-2 pb-2">
                            <x-ui.button :href="Route::has('member.login') ? route('member.login') : '#'"
                                variant="ghost" size="md" class="w-full justify-center">
                                Autentificare
                            </x-ui.button>
                            @if (Route::has('member.register'))
                                <x-ui.button :href="route('member.register')"
                                    variant="ghost" size="md" class="w-full justify-center">
                                    Înregistrare
                                </x-ui.button>
                            @endif
                        </div>
                    </div>
                    <x-ui.button :href="$membershipCta" variant="primary" size="md" class="w-full justify-center">
                        Abonament
                    </x-ui.button>
                </div>
            </div>
        </nav>
    </header>

    <main class="jf-safe-x">
        {{ $slot }}
    </main>

    <footer class="border-t border-zinc-200 bg-zinc-100 dark:border-white/8 dark:bg-canvas">
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
                        <span class="font-semibold tracking-tight text-zinc-900 dark:text-white">Julius Fitness</span>
                    </div>
                    <p class="mt-4 max-w-xs text-sm leading-relaxed text-zinc-600 dark:text-white/45">
                        Antrenează inteligent. Rămâi constant. Obține rezultate.
                    </p>
                </div>
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-white/35">Sală</h4>
                    <ul class="mt-4 space-y-2 text-sm text-zinc-600 dark:text-white/55">
                        <li><a href="#servicii" class="transition-colors hover:text-zinc-900 dark:hover:text-white">Servicii</a></li>
                        <li><a href="#program" class="transition-colors hover:text-zinc-900 dark:hover:text-white">Program</a></li>
                        <li><a href="#abonamente" class="transition-colors hover:text-zinc-900 dark:hover:text-white">Abonamente</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-white/35">Suport</h4>
                    <ul class="mt-4 space-y-2 text-sm text-zinc-600 dark:text-white/55">
                        <li><a href="#contact" class="transition-colors hover:text-zinc-900 dark:hover:text-white">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-white/35">Program</h4>
                    <ul class="mt-4 space-y-2 text-sm text-zinc-600 dark:text-white/55">
                        <li>Lun–Vin: 06:00 – 23:00</li>
                        <li>Sâm: 08:00 – 20:00</li>
                        <li>Dum: 08:00 – 18:00</li>
                    </ul>
                </div>
            </div>
            <div class="mt-10 flex flex-col gap-3 border-t border-zinc-200 pt-8 sm:flex-row sm:items-center sm:justify-between dark:border-white/8 sm:mt-12">
                <p class="text-sm text-zinc-500 dark:text-white/30">
                    &copy; {{ date('Y') }} Julius Fitness Gym
                </p>
                <x-studio.signature variant="inline" />
            </div>
        </div>
    </footer>

    <x-studio.html-comment />

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

        const navToggle = document.querySelector('[data-public-nav-toggle]');
        const navPanel = document.querySelector('[data-public-nav-panel]');
        const iconOpen = document.querySelector('[data-public-nav-icon-open]');
        const iconClose = document.querySelector('[data-public-nav-icon-close]');

        navToggle?.addEventListener('click', () => {
            const open = navPanel?.classList.toggle('is-open');
            navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            iconOpen?.classList.toggle('hidden', open);
            iconClose?.classList.toggle('hidden', !open);
            document.body.classList.toggle('overflow-hidden', open);
        });

        navPanel?.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                navPanel.classList.remove('is-open');
                navToggle?.setAttribute('aria-expanded', 'false');
                iconOpen?.classList.remove('hidden');
                iconClose?.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            });
        });
    </script>
</body>

</html>
