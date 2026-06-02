@php
    $services = [
        [
            'title' => 'Sală de forță',
            'desc' => 'Zone dedicate cu rack-uri, platforme și greutăți libere pentru antrenamente serioase.',
            'icon' => '<path d="M14.4 14.4 9.6 9.6" /><path d="M18.657 21.485a2 2 0 1 1-2.829-2.828l-1.767 1.768a2 2 0 1 1-2.829-2.829l6.364-6.364a2 2 0 1 1 2.829 2.829l-1.768 1.767a2 2 0 1 1 2.828 2.829z" /><path d="m21.5 21.5-1.4-1.4" /><path d="M3.9 3.9 2.5 2.5" /><path d="M6.404 12.768a2 2 0 1 1-2.829-2.829l1.768-1.767a2 2 0 1 1-2.828-2.829l2.828-2.828a2 2 0 1 1 2.829 2.828l1.767-1.768a2 2 0 1 1 2.829 2.829z" />',
        ],
        [
            'title' => 'Clase de grup',
            'desc' => 'HIIT, spinning, yoga și antrenament funcțional cu antrenori certificați, în fiecare zi.',
            'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />',
        ],
        [
            'title' => 'Antrenament personal',
            'desc' => 'Programe personalizate 1-la-1 pentru a ajunge mai repede la obiectivele tale.',
            'icon' => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" /><path d="m9 12 2 2 4-4" />',
        ],
        [
            'title' => 'Vestiare & recuperare',
            'desc' => 'Dusuri, vestiare spațioase și zonă de stretching pentru după antrenament.',
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
            'features' => ['Acces nelimitat', 'Toate clasele de grup', 'Vestiar & dușuri', '1 invitat / lună'],
        ],
        [
            'name' => 'Anual',
            'price' => '1.200',
            'period' => '/ an',
            'highlight' => false,
            'features' => ['Tot ce include Lunar', '2 luni gratuite', '2 ședințe PT', 'Prioritate la clase'],
        ],
    ];

    $schedule = [
        ['days' => 'Luni – Vineri', 'hours' => '06:00 – 23:00', 'note' => 'Sală + clase dimineața și seara'],
        ['days' => 'Sâmbătă', 'hours' => '08:00 – 20:00', 'note' => 'Clase de grup 10:00, 12:00, 18:00'],
        ['days' => 'Duminică', 'hours' => '08:00 – 18:00', 'note' => 'Acces sală; clase limitate'],
    ];

    $stats = [
        ['value' => '1.200+', 'label' => 'Membri activi'],
        ['value' => '40+', 'label' => 'Clase / săptămână'],
        ['value' => '15', 'label' => 'Antrenori'],
        ['value' => '7/7', 'label' => 'Deschis zilnic'],
    ];

    $membershipCta = Route::has('register') ? route('register') : (Route::has('login') ? route('login') : '#abonamente');
@endphp

<x-layouts.public title="Julius Fitness Gym — Antrenează inteligent, obține rezultate">
    {{-- Hero --}}
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-brand-50 via-white to-white dark:from-brand-600/10 dark:via-gray-950 dark:to-gray-950"></div>
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <x-ui.badge color="brand">Membri noi bineveniți</x-ui.badge>
                    <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl lg:text-6xl dark:text-white">
                        Julius Fitness Gym.<br>
                        <span class="text-brand-600 dark:text-brand-400">Rezultate reale.</span>
                    </h1>
                    <p class="mt-6 max-w-lg text-lg text-gray-600 dark:text-gray-300">
                        Sala ta de forță și condiționare în oraș. Echipament modern, antrenori dedicați
                        și o comunitate care te ține pe drumul cel bun.
                    </p>
                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <x-ui.button :href="$membershipCta" variant="primary" size="lg">Începe abonamentul</x-ui.button>
                        <x-ui.button href="#servicii" variant="secondary" size="lg">Descoperă sala</x-ui.button>
                    </div>
                </div>

                <div class="relative">
                    <div class="aspect-[4/3] overflow-hidden rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 shadow-xl">
                        <div class="flex h-full w-full flex-col items-center justify-center gap-3 px-8 text-center">
                            <svg class="h-24 w-24 text-white/30" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14.4 14.4 9.6 9.6" /><path d="M18.657 21.485a2 2 0 1 1-2.829-2.828l-1.767 1.768a2 2 0 1 1-2.829-2.829l6.364-6.364a2 2 0 1 1 2.829 2.829l-1.768 1.767a2 2 0 1 1 2.828 2.829z" /><path d="m21.5 21.5-1.4-1.4" /><path d="M3.9 3.9 2.5 2.5" /><path d="M6.404 12.768a2 2 0 1 1-2.829-2.829l1.768-1.767a2 2 0 1 1-2.828-2.829l2.828-2.828a2 2 0 1 1 2.829 2.828l1.767-1.768a2 2 0 1 1 2.829 2.829z" />
                            </svg>
                            <p class="text-sm font-medium text-white/80">Forță · Cardio · Clase · PT</p>
                        </div>
                    </div>
                </div>
            </div>

            <dl class="mt-16 grid grid-cols-2 gap-6 border-t border-gray-200 pt-10 sm:grid-cols-4 dark:border-gray-800">
                @foreach ($stats as $stat)
                    <div class="text-center">
                        <dt class="text-3xl font-bold text-gray-900 dark:text-white">{{ $stat['value'] }}</dt>
                        <dd class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>
    </section>

    {{-- Servicii --}}
    <section id="servicii" class="mx-auto max-w-7xl scroll-mt-20 px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-white">
                Servicii
            </h2>
            <p class="mt-4 text-gray-600 dark:text-gray-300">
                Tot ce ai nevoie pentru antrenament — sub un singur acoperiș.
            </p>
        </div>

        <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($services as $service)
                <x-ui.card>
                    <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-600/15 dark:text-brand-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            {!! $service['icon'] !!}
                        </svg>
                    </span>
                    <h3 class="mt-4 font-semibold text-gray-900 dark:text-white">{{ $service['title'] }}</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $service['desc'] }}</p>
                </x-ui.card>
            @endforeach
        </div>
    </section>

    {{-- Program sală --}}
    <section id="program" class="scroll-mt-20 bg-gray-50 py-20 dark:bg-gray-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-white">
                    Program sală
                </h2>
                <p class="mt-4 text-gray-600 dark:text-gray-300">
                    Deschis în fiecare zi — antrenează-te când ți se potrivește.
                </p>
            </div>

            <div class="mx-auto mt-12 max-w-3xl divide-y divide-gray-200 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:divide-gray-800 dark:border-gray-800 dark:bg-gray-950">
                @foreach ($schedule as $row)
                    <div class="flex flex-col gap-2 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $row['days'] }}</p>
                            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">{{ $row['note'] }}</p>
                        </div>
                        <p class="text-lg font-bold tabular-nums text-brand-600 dark:text-brand-400">{{ $row['hours'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Abonamente --}}
    <section id="abonamente" class="mx-auto max-w-7xl scroll-mt-20 px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-white">
                Abonamente
            </h2>
            <p class="mt-4 text-gray-600 dark:text-gray-300">Prețuri clare, fără surprize. Alege ce ți se potrivește.</p>
        </div>

        <div class="mt-12 grid grid-cols-1 gap-6 lg:grid-cols-3">
            @foreach ($plans as $plan)
                <div class="relative flex flex-col rounded-2xl border bg-white p-6 shadow-sm dark:bg-gray-950 {{ $plan['highlight'] ? 'border-brand-500 ring-1 ring-brand-500' : 'border-gray-200 dark:border-gray-800' }}">
                    @if ($plan['highlight'])
                        <span class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <x-ui.badge color="brand">Cel mai popular</x-ui.badge>
                        </span>
                    @endif
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $plan['name'] }}</h3>
                    <div class="mt-4 flex items-baseline gap-1">
                        <span class="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ $plan['price'] }}</span>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">lei {{ $plan['period'] }}</span>
                    </div>
                    <ul class="mt-6 flex-1 space-y-3 text-sm">
                        @foreach ($plan['features'] as $item)
                            <li class="flex items-start gap-2 text-gray-600 dark:text-gray-300">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-600 dark:text-brand-400" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M20 6 9 17l-5-5" />
                                </svg>
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                    <div class="mt-8 pt-2">
                        <x-ui.button :href="$membershipCta" :variant="$plan['highlight'] ? 'primary' : 'secondary'" size="lg"
                            class="w-full">
                            Alege {{ $plan['name'] }}
                        </x-ui.button>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- CTA abonament --}}
    <section id="contact" class="scroll-mt-20 px-4 pb-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl overflow-hidden rounded-3xl bg-brand-600 px-6 py-14 text-center shadow-xl sm:px-12">
            <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Gata să începi?</h2>
            <p class="mx-auto mt-4 max-w-xl text-brand-50">
                Vino la o vizită gratuită sau creează-ți contul online și alege abonamentul potrivit.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <x-ui.button :href="$membershipCta" variant="secondary" size="lg"
                    class="!bg-white !text-brand-700 hover:!bg-brand-50">
                    Abonament acum
                </x-ui.button>
                <a href="tel:+40000000000"
                    class="inline-flex items-center justify-center rounded-lg border border-white/30 px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-white/10">
                    Sună-ne
                </a>
            </div>
        </div>
    </section>
</x-layouts.public>
