@php
    $services = [
        [
            'title' => 'Sală de forță',
            'desc' => 'Rack-uri, platforme și greutăți libere pentru antrenamente serioase.',
            'icon' => '<path d="M14.4 14.4 9.6 9.6" /><path d="M18.657 21.485a2 2 0 1 1-2.829-2.828l-1.767 1.768a2 2 0 1 1-2.829-2.829l6.364-6.364a2 2 0 1 1 2.829 2.829l-1.768 1.767a2 2 0 1 1 2.828 2.829z" /><path d="m21.5 21.5-1.4-1.4" /><path d="M3.9 3.9 2.5 2.5" /><path d="M6.404 12.768a2 2 0 1 1-2.829-2.829l1.768-1.767a2 2 0 1 1-2.828-2.829l2.828-2.828a2 2 0 1 1 2.829 2.828l1.767-1.768a2 2 0 1 1 2.829 2.829z" />',
        ],
        [
            'title' => 'Clase de grup',
            'desc' => 'HIIT, spinning, yoga și funcțional — antrenori certificați, zilnic.',
            'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />',
        ],
        [
            'title' => 'Antrenament personal',
            'desc' => 'Programe 1-la-1, obiective clare, progres măsurabil.',
            'icon' => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" /><path d="m9 12 2 2 4-4" />',
        ],
        [
            'title' => 'Recuperare',
            'desc' => 'Vestiare spațioase, dușuri și zonă de stretching post-workout.',
            'icon' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" />',
        ],
    ];

    $plans = [
        [
            'name' => 'Zi',
            'price' => '50',
            'period' => '/ vizită',
            'highlight' => false,
            'features' => ['Acces complet o zi', 'Vestiar & dușuri', 'Fără contract'],
        ],
        [
            'name' => 'Lunar',
            'price' => '150',
            'period' => '/ lună',
            'highlight' => true,
            'features' => ['Acces nelimitat', 'Toate clasele', 'Vestiar & dușuri', '1 invitat / lună'],
        ],
        [
            'name' => 'Anual',
            'price' => '1.200',
            'period' => '/ an',
            'highlight' => false,
            'features' => ['Tot din Lunar', '2 luni gratuite', '2 ședințe PT', 'Prioritate clase'],
        ],
    ];

    $schedule = [
        ['days' => 'Luni – Vineri', 'hours' => '06:00 – 23:00', 'note' => 'Sală + clase dimineața și seara'],
        ['days' => 'Sâmbătă', 'hours' => '08:00 – 20:00', 'note' => 'Clase: 10:00 · 12:00 · 18:00'],
        ['days' => 'Duminică', 'hours' => '08:00 – 18:00', 'note' => 'Acces sală · clase limitate'],
    ];

    $stats = [
        ['value' => '1.200+', 'label' => 'Membri activi'],
        ['value' => '40+', 'label' => 'Clase / săptămână'],
        ['value' => '15', 'label' => 'Antrenori'],
        ['value' => '7/7', 'label' => 'Deschis zilnic'],
    ];

    $membershipCta = Route::has('register') ? route('register') : (Route::has('login') ? route('login') : '#abonamente');
@endphp

<x-layouts.public title="Julius Fitness Gym">
    {{-- Cinematic hero — full viewport --}}
    <section class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden">
        <div class="absolute inset-0 jf-hero-gradient"></div>
        <div class="absolute inset-0 jf-hero-noise opacity-60"></div>
        <div
            class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,transparent_0%,#000000_72%)]">
        </div>

        <div class="relative z-10 mx-auto max-w-5xl px-4 pb-24 pt-[max(5rem,calc(env(safe-area-inset-top,0px)+4rem))] text-center sm:px-6 sm:pb-32 sm:pt-28">
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-white/40">Julius Fitness Gym</p>
            <h1
                class="text-balance mt-6 text-4xl font-extrabold leading-[1.05] tracking-tight text-white sm:text-6xl lg:text-8xl">
                Antrenează.<br>
                <span class="text-brand-400">Transformă.</span>
            </h1>
            <p class="mx-auto mt-8 max-w-xl text-lg leading-relaxed text-white/55 sm:text-xl">
                Forță. Condiționare. Comunitate. Sala unde fiecare repetiție contează.
            </p>
            <div class="mt-12 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <x-ui.button :href="$membershipCta" variant="primary" size="lg">
                    Începe abonamentul
                </x-ui.button>
                <x-ui.button href="#servicii" variant="secondary" size="lg">
                    Explorează
                </x-ui.button>
            </div>
        </div>

        <a href="#servicii"
            class="absolute bottom-10 left-1/2 z-10 flex -translate-x-1/2 flex-col items-center gap-2 text-white/30 transition-colors duration-200 hover:text-white/60"
            aria-label="Scroll">
            <span class="text-[10px] font-medium uppercase tracking-[0.25em]">Scroll</span>
            <svg class="h-5 w-5 animate-bounce" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m6 9 6 6 6-6" />
            </svg>
        </a>
    </section>

    {{-- Stats --}}
    <section class="border-y border-zinc-200 bg-zinc-100 dark:border-white/8 dark:bg-canvas">
        <dl class="mx-auto grid max-w-7xl grid-cols-2 gap-8 px-4 py-16 sm:grid-cols-4 sm:px-6 lg:px-8">
            @foreach ($stats as $stat)
                <div class="jf-reveal text-center" style="transition-delay: {{ $loop->index * 60 }}ms">
                    <dt class="text-3xl font-bold tracking-tight text-zinc-900 sm:text-4xl dark:text-white">{{ $stat['value'] }}</dt>
                    <dd class="mt-2 text-sm text-zinc-500 dark:text-white/40">{{ $stat['label'] }}</dd>
                </div>
            @endforeach
        </dl>
    </section>

    {{-- Servicii --}}
    <section id="servicii" class="mx-auto max-w-7xl scroll-mt-24 px-4 py-28 sm:px-6 lg:px-8">
        <div class="jf-reveal mx-auto max-w-2xl text-center">
            <h2 class="text-4xl font-bold tracking-tight text-zinc-900 sm:text-5xl dark:text-white">Servicii</h2>
            <p class="mt-5 text-lg text-zinc-600 dark:text-white/50">Tot ce ai nevoie — un singur loc.</p>
        </div>

        <div class="mt-16 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($services as $service)
                <x-ui.card class="jf-reveal" style="transition-delay: {{ $loop->index * 80 }}ms">
                    <span
                        class="flex h-11 w-11 items-center justify-center rounded-full border border-brand-500/25 bg-brand-500/10 text-brand-500 dark:text-brand-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            {!! $service['icon'] !!}
                        </svg>
                    </span>
                    <h3 class="mt-5 text-base font-semibold text-zinc-900 dark:text-white">{{ $service['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-white/45">{{ $service['desc'] }}</p>
                </x-ui.card>
            @endforeach
        </div>
    </section>

    {{-- Program --}}
    <section id="program" class="scroll-mt-24 border-t border-white/8 bg-black py-28">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="jf-reveal mx-auto max-w-2xl text-center">
                <h2 class="text-4xl font-bold tracking-tight text-white sm:text-5xl">Program sală</h2>
                <p class="mt-5 text-lg text-white/50">Deschis zilnic. Antrenează-te când vrei.</p>
            </div>

            <div class="jf-reveal mx-auto mt-14 max-w-3xl overflow-hidden rounded-2xl border border-white/8 bg-surface-elevated">
                @foreach ($schedule as $row)
                    <div
                        class="flex flex-col gap-2 border-b border-white/6 px-6 py-6 last:border-b-0 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-semibold text-white">{{ $row['days'] }}</p>
                            <p class="mt-1 text-sm text-white/40">{{ $row['note'] }}</p>
                        </div>
                        <p class="text-xl font-bold tabular-nums tracking-tight text-brand-400">{{ $row['hours'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Abonamente --}}
    <section id="abonamente" class="mx-auto max-w-7xl scroll-mt-24 px-4 py-28 sm:px-6 lg:px-8">
        <div class="jf-reveal mx-auto max-w-2xl text-center">
            <h2 class="text-4xl font-bold tracking-tight text-zinc-900 sm:text-5xl dark:text-white">Abonamente</h2>
            <p class="mt-5 text-lg text-zinc-600 dark:text-white/50">Prețuri clare. Fără surprize.</p>
        </div>

        <div class="mt-16 grid grid-cols-1 gap-5 lg:grid-cols-3">
            @foreach ($plans as $plan)
                <div
                    class="jf-reveal relative flex flex-col rounded-2xl border p-8 transition-all duration-200 {{ $plan['highlight'] ? 'border-brand-500/40 bg-brand-500/5 jf-glow-accent' : 'border-zinc-200 bg-white hover:border-zinc-300 dark:border-white/8 dark:bg-surface-elevated dark:hover:border-white/15' }}"
                    style="transition-delay: {{ $loop->index * 80 }}ms">
                    @if ($plan['highlight'])
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <x-ui.badge color="brand">Popular</x-ui.badge>
                        </span>
                    @endif
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $plan['name'] }}</h3>
                    <div class="mt-5 flex items-baseline gap-1">
                        <span class="text-5xl font-extrabold tracking-tight text-zinc-900 dark:text-white">{{ $plan['price'] }}</span>
                        <span class="text-sm font-medium text-zinc-500 dark:text-white/40">lei {{ $plan['period'] }}</span>
                    </div>
                    <ul class="mt-8 flex-1 space-y-3 text-sm text-zinc-600 dark:text-white/55">
                        @foreach ($plan['features'] as $item)
                            <li class="flex items-start gap-2">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-400" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M20 6 9 17l-5-5" />
                                </svg>
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-10">
                        <x-ui.button :href="$membershipCta" :variant="$plan['highlight'] ? 'primary' : 'secondary'" size="lg"
                            class="w-full">
                            Alege {{ $plan['name'] }}
                        </x-ui.button>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- CTA --}}
    <section id="contact" class="scroll-mt-24 px-4 pb-28 sm:px-6 lg:px-8">
        <div
            class="jf-reveal mx-auto max-w-5xl overflow-hidden rounded-3xl border border-zinc-200 bg-white px-6 py-20 text-center sm:px-14 dark:border-white/10 dark:bg-surface-elevated">
            <h2 class="text-4xl font-bold tracking-tight text-zinc-900 sm:text-5xl dark:text-white">Gata să începi?</h2>
            <p class="mx-auto mt-5 max-w-lg text-lg text-zinc-600 dark:text-white/50">
                Creează-ți contul sau vino la o vizită. Primul pas e cel mai greu.
            </p>
            <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                <x-ui.button :href="$membershipCta" variant="primary" size="lg">
                    Abonament acum
                </x-ui.button>
                <x-ui.button href="tel:+40000000000" variant="secondary" size="lg">
                    Sună-ne
                </x-ui.button>
            </div>
        </div>
    </section>
</x-layouts.public>
