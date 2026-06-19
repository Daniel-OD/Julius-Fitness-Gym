@php
    $membershipCta = auth('member')->check()
        ? route('member.plans')
        : url('/#abonamente');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <x-layouts.partials.head-meta
        :title="$title ?? __('public.meta.title')"
        :description="$description ?? __('public.meta.description')" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="jf-min-h-screen bg-zinc-50 font-sans text-zinc-900 antialiased dark:bg-black dark:text-white">
    <header
        class="jf-safe-t fixed inset-x-0 top-0 z-50 border-b border-transparent bg-transparent transition-[background-color,border-color,backdrop-filter] duration-300"
        data-public-header>
        <div class="jf-safe-x mx-auto flex h-14 max-w-7xl items-center gap-2 px-4 sm:h-16 sm:gap-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-2.5">
                <x-application-mark class="h-9 w-9 shrink-0" />
                <span class="truncate text-sm font-semibold tracking-tight text-zinc-900 dark:text-white">Julius Fitness</span>
            </a>

            <button type="button" data-public-nav-toggle
                class="jf-touch-target ml-auto inline-flex items-center justify-center rounded-full border border-zinc-200 p-2 text-zinc-700 dark:border-white/15 dark:text-white md:hidden"
                aria-expanded="false" aria-controls="public-mobile-nav" aria-label="{{ __('public.nav.menu') }}">
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
                    class="rounded-full px-3 py-2 text-sm font-medium text-zinc-600 transition-colors duration-200 hover:bg-zinc-100 hover:text-zinc-900 dark:text-white/60 dark:hover:bg-white/5 dark:hover:text-white">{{ __('public.nav.services') }}</a>
                <a href="#program"
                    class="rounded-full px-3 py-2 text-sm font-medium text-zinc-600 transition-colors duration-200 hover:bg-zinc-100 hover:text-zinc-900 dark:text-white/60 dark:hover:bg-white/5 dark:hover:text-white">{{ __('public.nav.schedule') }}</a>
                <a href="#abonamente"
                    class="rounded-full px-3 py-2 text-sm font-medium text-zinc-600 transition-colors duration-200 hover:bg-zinc-100 hover:text-zinc-900 dark:text-white/60 dark:hover:bg-white/5 dark:hover:text-white">{{ __('public.nav.memberships') }}</a>
                <a href="#contact"
                    class="rounded-full px-3 py-2 text-sm font-medium text-zinc-600 transition-colors duration-200 hover:bg-zinc-100 hover:text-zinc-900 dark:text-white/60 dark:hover:bg-white/5 dark:hover:text-white">{{ __('public.nav.contact') }}</a>
            </nav>

            <div class="ml-auto hidden items-center gap-2 md:flex">
                <x-public.locale-switcher />
                <button type="button" data-theme-toggle
                    class="rounded-full border border-zinc-200 px-3 py-1.5 text-xs font-medium text-zinc-600 transition-colors hover:bg-zinc-100 dark:border-white/15 dark:text-white/70 dark:hover:bg-white/5"
                    aria-label="{{ __('public.ui.toggle_theme') }}">
                    <span class="hidden dark:inline">{{ __('public.ui.light_mode') }}</span>
                    <span class="dark:hidden">{{ __('public.ui.dark_mode') }}</span>
                </button>
                @auth('member')
                    <x-ui.button :href="route('member.dashboard')" variant="ghost" size="md">
                        {{ __('public.nav.portal') }}
                    </x-ui.button>
                @else
                    @if (Route::has('member.login'))
                        <x-ui.button :href="route('member.login')" variant="ghost" size="md">
                            {{ __('public.nav.login') }}
                        </x-ui.button>
                    @endif
                    <x-ui.button :href="$membershipCta" variant="primary" size="md" class="text-xs sm:text-sm">
                        {{ __('public.nav.membership_cta') }}
                    </x-ui.button>
                @endauth
            </div>
        </div>

        <nav id="public-mobile-nav" data-public-nav-panel
            class="jf-mobile-nav-panel jf-safe-x border-t border-zinc-200 bg-white/95 backdrop-blur-xl dark:border-white/10 dark:bg-black/90 md:hidden">
            <div class="flex flex-col gap-1 px-4 py-4">
                <a href="#servicii"
                    class="jf-touch-target rounded-xl px-4 py-3 text-base font-medium text-zinc-700 dark:text-white/80">{{ __('public.nav.services') }}</a>
                <a href="#program"
                    class="jf-touch-target rounded-xl px-4 py-3 text-base font-medium text-zinc-700 dark:text-white/80">{{ __('public.nav.schedule') }}</a>
                <a href="#abonamente"
                    class="jf-touch-target rounded-xl px-4 py-3 text-base font-medium text-zinc-700 dark:text-white/80">{{ __('public.nav.memberships') }}</a>
                <a href="#contact"
                    class="jf-touch-target rounded-xl px-4 py-3 text-base font-medium text-zinc-700 dark:text-white/80">{{ __('public.nav.contact') }}</a>
                <div class="mt-2 flex flex-col gap-2 border-t border-zinc-200 pt-4 dark:border-white/10">
                    <div class="px-2">
                        <x-public.locale-switcher />
                    </div>
                    @auth('member')
                        <x-ui.button :href="route('member.dashboard')" variant="ghost" size="md" class="w-full justify-center">
                            {{ __('public.nav.portal') }}
                        </x-ui.button>
                    @else
                        @if (Route::has('member.login'))
                            <x-ui.button :href="route('member.login')" variant="ghost" size="md" class="w-full justify-center">
                                {{ __('public.nav.login') }}
                            </x-ui.button>
                        @endif
                        <x-ui.button :href="$membershipCta" variant="primary" size="md" class="w-full justify-center">
                            {{ __('public.nav.membership_cta') }}
                        </x-ui.button>
                    @endauth
                </div>
            </div>
        </nav>
    </header>

    <main class="jf-safe-x">
        {{ $slot }}
    </main>

    <footer class="border-t border-zinc-200 bg-zinc-50 dark:border-white/8 dark:bg-canvas">
        <div class="mx-auto max-w-7xl px-4 py-12 sm:py-16 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 gap-8 sm:gap-10 md:grid-cols-4">
                <div class="col-span-2 md:col-span-1">
                    <div class="flex items-center gap-2.5">
                        <x-application-mark class="h-8 w-8 shrink-0" />
                        <span class="font-semibold tracking-tight text-zinc-900 dark:text-white">Julius Fitness</span>
                    </div>
                    <p class="mt-4 max-w-xs text-sm leading-relaxed text-zinc-600 dark:text-white/45">
                        {{ __('public.footer.tagline') }}
                    </p>
                </div>
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-white/35">{{ __('public.footer.gym') }}</h4>
                    <ul class="mt-4 space-y-2 text-sm text-zinc-600 dark:text-white/55">
                        <li><a href="#servicii" class="transition-colors hover:text-zinc-900 dark:hover:text-white">{{ __('public.nav.services') }}</a></li>
                        <li><a href="#program" class="transition-colors hover:text-zinc-900 dark:hover:text-white">{{ __('public.nav.schedule') }}</a></li>
                        <li><a href="#abonamente" class="transition-colors hover:text-zinc-900 dark:hover:text-white">{{ __('public.nav.memberships') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-white/35">{{ __('public.footer.support') }}</h4>
                    <ul class="mt-4 space-y-2 text-sm text-zinc-600 dark:text-white/55">
                        <li><a href="#contact" class="transition-colors hover:text-zinc-900 dark:hover:text-white">{{ __('public.nav.contact') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-white/35">{{ __('public.footer.hours_title') }}</h4>
                    <ul class="mt-4 space-y-2 text-sm text-zinc-600 dark:text-white/55">
                        <li>{{ __('public.footer.hours.weekdays') }}</li>
                        <li>{{ __('public.footer.hours.saturday') }}</li>
                        <li>{{ __('public.footer.hours.sunday') }}</li>
                    </ul>
                </div>
            </div>
            <div class="mt-10 flex flex-col gap-3 border-t border-zinc-200 pt-8 sm:flex-row sm:items-center sm:justify-between dark:border-white/8 sm:mt-12">
                <p class="text-sm text-zinc-500 dark:text-white/30">
                    &copy; {{ date('Y') }} Julius Fitness Gym
                </p>
            </div>
        </div>
    </footer>

    <script>
        const header = document.querySelector('[data-public-header]');
        const onScroll = () => {
            if (!header) return;
            if (window.scrollY > 24) {
                header.classList.add(
                    'border-zinc-200/80', 'bg-white/85', 'backdrop-blur-xl',
                    'dark:border-white/8', 'dark:bg-black/75',
                );
            } else {
                header.classList.remove(
                    'border-zinc-200/80', 'bg-white/85', 'backdrop-blur-xl',
                    'dark:border-white/8', 'dark:bg-black/75',
                );
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
