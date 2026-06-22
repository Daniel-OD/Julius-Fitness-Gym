@php
    $serviceIcons = [
        'strength' => '<path d="M14.4 14.4 9.6 9.6" /><path d="M18.657 21.485a2 2 0 1 1-2.829-2.828l-1.767 1.768a2 2 0 1 1-2.829-2.829l6.364-6.364a2 2 0 1 1 2.829 2.829l-1.768 1.767a2 2 0 1 1 2.828 2.829z" /><path d="m21.5 21.5-1.4-1.4" /><path d="M3.9 3.9 2.5 2.5" /><path d="M6.404 12.768a2 2 0 1 1-2.829-2.829l1.768-1.767a2 2 0 1 1-2.828-2.829l2.828-2.828a2 2 0 1 1 2.829 2.828l1.767-1.768a2 2 0 1 1 2.829 2.829z" />',
        'groups' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />',
        'personal' => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" /><path d="m9 12 2 2 4-4" />',
        'recovery' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" />',
        'cardio' => '<path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2" />',
        'nutrition' => '<path d="M2.27 21.7s9.87-3.5 12.73-6.36a4.5 4.5 0 0 0-6.36-6.37C5.77 11.84 2.27 21.7 2.27 21.7zM8.64 14l-2.05-2.04M15.34 15l-2.46-2.46" /><path d="M22 9s-1.3-2-3.5-2C16.86 7 15 9 15 9s1.3 2 3.5 2S22 9 22 9z" /><path d="M15 2s-2 1.3-2 3.5S15 9 15 9s2-1.3 2-3.5S15 2 15 2z" />',
    ];

    $stats = collect(['members', 'classes', 'trainers', 'open'])
        ->map(fn (string $key): array => [
            'value' => __("public.stats.{$key}.value"),
            'label' => __("public.stats.{$key}.label"),
        ])
        ->all();

    $schedule = trans('public.schedule.rows');

    $plansSectionHref = auth('member')->check() ? route('member.plans') : '#abonamente';

    $planSignupUrl = fn ($plan) => auth('member')->check()
        ? route('member.plans')
        : route('member.register', ['plan' => $plan->id]);

    $planPeriod = fn ($plan) => match (true) {
        ($plan->days ?? 0) <= 1 => __('public.plans.per_visit'),
        ($plan->days ?? 0) <= 31 => __('public.plans.per_month'),
        default => __('public.plans.per_days', ['days' => $plan->days]),
    };
@endphp

<x-layouts.public :title="__('public.meta.title')">
    <section class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden">
        <div class="absolute inset-0 jf-hero-gradient"></div>
        <div class="absolute inset-0 jf-hero-noise opacity-60"></div>
        <div class="jf-hero-vignette absolute inset-0"></div>

        <div class="relative z-10 mx-auto max-w-5xl px-4 pb-24 pt-[max(5rem,calc(env(safe-area-inset-top,0px)+4rem))] text-center sm:px-6 sm:pb-32 sm:pt-28">
            <p class="text-xs font-semibold uppercase tracking-[0.35em] text-zinc-500 dark:text-white/40">{{ __('public.hero.eyebrow') }}</p>
            <h1 class="text-balance mt-6 text-4xl font-extrabold leading-[1.05] tracking-tight text-zinc-900 sm:text-6xl lg:text-8xl dark:text-white">
                {{ __('public.hero.title_train') }}<br>
                <span class="text-brand-500 dark:text-brand-400">{{ __('public.hero.title_transform') }}</span>
            </h1>
            <p class="mx-auto mt-8 max-w-xl text-lg leading-relaxed text-zinc-600 sm:text-xl dark:text-white/55">
                {{ __('public.hero.subtitle') }}
            </p>
            <div class="mt-12 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <x-ui.button :href="$plansSectionHref" variant="primary" size="lg">
                    {{ __('public.hero.cta_start') }}
                </x-ui.button>
                <x-ui.button href="#servicii" variant="secondary" size="lg">
                    {{ __('public.hero.cta_explore') }}
                </x-ui.button>
            </div>
        </div>

        <a href="#servicii"
            class="absolute bottom-10 left-1/2 z-10 flex -translate-x-1/2 flex-col items-center gap-2 text-zinc-400 transition-colors duration-200 hover:text-zinc-600 dark:text-white/30 dark:hover:text-white/60"
            aria-label="{{ __('public.ui.scroll') }}">
            <span class="text-[10px] font-medium uppercase tracking-[0.25em]">{{ __('public.ui.scroll') }}</span>
            <svg class="h-5 w-5 animate-bounce" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="m6 9 6 6 6-6" />
            </svg>
        </a>
    </section>

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

    <section id="servicii" class="mx-auto max-w-7xl scroll-mt-24 px-4 py-28 sm:px-6 lg:px-8">
        <div class="jf-reveal mx-auto max-w-2xl text-center">
            <h2 class="text-4xl font-bold tracking-tight text-zinc-900 sm:text-5xl dark:text-white">{{ __('public.services.title') }}</h2>
            <p class="mt-5 text-lg text-zinc-600 dark:text-white/50">{{ __('public.services.subtitle') }}</p>
        </div>

        <div class="mt-16 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($services as $service)
                @php
                    $hasImages = ! empty($service->images);
                    $iconSvg = $serviceIcons[$service->icon ?? ''] ?? $serviceIcons['strength'];
                    $galleryImages = collect($service->images ?? [])->map(fn ($path) => asset('storage/' . $path))->values()->toJson();
                @endphp
                <div
                    x-data="{ open: false, idx: 0, images: {{ $galleryImages }} }"
                    class="jf-reveal {{ $hasImages ? 'cursor-pointer' : '' }}"
                    style="transition-delay: {{ $loop->index * 80 }}ms"
                    @if($hasImages) @click="open = true; idx = 0" @endif
                >
                    <x-ui.card class="h-full">
                        <span class="flex h-11 w-11 items-center justify-center rounded-full border border-brand-500/25 bg-brand-500/10 text-brand-500 dark:text-brand-400">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round">
                                {!! $iconSvg !!}
                            </svg>
                        </span>
                        <h3 class="mt-5 text-base font-semibold text-zinc-900 dark:text-white">{{ $service->name }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-zinc-600 dark:text-white/45">{{ $service->description }}</p>
                        @if($hasImages)
                            <p class="mt-3 flex items-center gap-1 text-xs font-medium text-brand-500 dark:text-brand-400">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2" /><circle cx="9" cy="9" r="2" /><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                </svg>
                                {{ count($service->images) }} {{ __('public.services.photos') }}
                            </p>
                        @endif
                    </x-ui.card>

                    @if($hasImages)
                        <div
                            x-show="open"
                            x-transition:enter="transition duration-200 ease-out"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition duration-150 ease-in"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 p-4"
                            @click.stop="open = false"
                            @keydown.escape.window="open = false"
                            style="display: none"
                        >
                            <div class="relative flex w-full max-w-4xl flex-col items-center gap-4" @click.stop>
                                <button
                                    @click="open = false"
                                    class="absolute -top-10 right-0 flex h-8 w-8 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20"
                                    aria-label="{{ __('public.ui.close') }}"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M18 6 6 18M6 6l12 12" />
                                    </svg>
                                </button>

                                <div class="relative w-full overflow-hidden rounded-2xl bg-zinc-900">
                                    <img
                                        :src="images[idx]"
                                        :alt="'{{ e($service->name) }} ' + (idx + 1)"
                                        class="max-h-[70vh] w-full object-contain"
                                    />

                                    <template x-if="images.length > 1">
                                        <div>
                                            <button
                                                @click="idx = (idx - 1 + images.length) % images.length"
                                                class="absolute left-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-black/50 text-white transition hover:bg-black/70"
                                                aria-label="{{ __('public.ui.prev') }}"
                                            >
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="m15 18-6-6 6-6" />
                                                </svg>
                                            </button>
                                            <button
                                                @click="idx = (idx + 1) % images.length"
                                                class="absolute right-3 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-black/50 text-white transition hover:bg-black/70"
                                                aria-label="{{ __('public.ui.next') }}"
                                            >
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="m9 18 6-6-6-6" />
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>

                                <template x-if="images.length > 1">
                                    <div class="flex gap-2">
                                        <template x-for="(img, i) in images" :key="i">
                                            <button
                                                @click="idx = i"
                                                :class="i === idx ? 'ring-2 ring-white opacity-100' : 'opacity-50 hover:opacity-75'"
                                                class="h-12 w-12 overflow-hidden rounded-lg transition"
                                            >
                                                <img :src="img" class="h-full w-full object-cover" />
                                            </button>
                                        </template>
                                    </div>
                                </template>

                                <p class="text-sm text-white/60">
                                    {{ $service->name }} —
                                    <span x-text="idx + 1"></span> / <span x-text="images.length"></span>
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <section id="program" class="scroll-mt-24 border-t border-zinc-200 bg-zinc-50 py-28 dark:border-white/8 dark:bg-black">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="jf-reveal mx-auto max-w-2xl text-center">
                <h2 class="text-4xl font-bold tracking-tight text-zinc-900 sm:text-5xl dark:text-white">{{ __('public.schedule.title') }}</h2>
                <p class="mt-5 text-lg text-zinc-600 dark:text-white/50">{{ __('public.schedule.subtitle') }}</p>
            </div>

            <div class="jf-reveal mx-auto mt-14 max-w-3xl overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-white/8 dark:bg-surface-elevated dark:shadow-none">
                @foreach ($schedule as $row)
                    <div class="flex flex-col gap-2 border-b border-zinc-100 px-6 py-6 last:border-b-0 sm:flex-row sm:items-center sm:justify-between dark:border-white/6">
                        <div>
                            <p class="font-semibold text-zinc-900 dark:text-white">{{ $row['days'] }}</p>
                            <p class="mt-1 text-sm text-zinc-500 dark:text-white/40">{{ $row['note'] }}</p>
                        </div>
                        <p class="text-xl font-bold tabular-nums tracking-tight text-brand-500 dark:text-brand-400">{{ $row['hours'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @if ($gymSchedule->isNotEmpty())
    <section id="clase" class="scroll-mt-24 border-t border-zinc-200 py-28 dark:border-white/8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="jf-reveal mx-auto max-w-2xl text-center">
                <h2 class="text-4xl font-bold tracking-tight text-zinc-900 sm:text-5xl dark:text-white">{{ __('public.classes.title') }}</h2>
                <p class="mt-5 text-lg text-zinc-600 dark:text-white/50">{{ __('public.classes.subtitle') }}</p>
            </div>
            @php
                $dayNames = [
                    0 => __('app.classes.days.sunday'),
                    1 => __('app.classes.days.monday'),
                    2 => __('app.classes.days.tuesday'),
                    3 => __('app.classes.days.wednesday'),
                    4 => __('app.classes.days.thursday'),
                    5 => __('app.classes.days.friday'),
                    6 => __('app.classes.days.saturday'),
                ];
            @endphp
            <div class="mt-16 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-7">
                @foreach (range(0, 6) as $day)
                    @php $slots = $gymSchedule->get($day, collect()); @endphp
                    <div class="jf-reveal rounded-2xl border border-zinc-200 bg-white p-4 dark:border-white/8 dark:bg-surface-elevated"
                         style="transition-delay: {{ $day * 60 }}ms">
                        <p class="mb-3 text-xs font-bold uppercase tracking-wider text-zinc-500 dark:text-white/40">{{ $dayNames[$day] }}</p>
                        @if ($slots->isEmpty())
                            <p class="text-xs text-zinc-300 dark:text-white/20">—</p>
                        @else
                            <div class="space-y-2">
                                @foreach ($slots as $slot)
                                    <div class="rounded-xl p-2 text-xs" style="border-left: 3px solid {{ $slot['color'] ?? '#6366f1' }}; background: {{ $slot['color'] ?? '#6366f1' }}12">
                                        <p class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $slot['name'] }}</p>
                                        <p class="text-zinc-500">{{ $slot['start_time'] }}</p>
                                        @if ($slot['instructor'])
                                            <p class="text-zinc-400">{{ $slot['instructor'] }}</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="mt-10 text-center">
                <a href="{{ route('member.classes.index') }}"
                   class="inline-block rounded-2xl bg-zinc-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100">
                    {{ __('public.classes.view_full_schedule') }}
                </a>
            </div>
        </div>
    </section>
    @endif

    <section id="abonamente" class="mx-auto max-w-7xl scroll-mt-24 px-4 py-28 sm:px-6 lg:px-8">
        <div class="jf-reveal mx-auto max-w-2xl text-center">
            <h2 class="text-4xl font-bold tracking-tight text-zinc-900 sm:text-5xl dark:text-white">{{ __('public.plans.title') }}</h2>
            <p class="mt-5 text-lg text-zinc-600 dark:text-white/50">{{ __('public.plans.subtitle') }}</p>
        </div>

        @if ($plans->isEmpty())
            <p class="jf-reveal mt-16 text-center text-sm text-zinc-600 dark:text-white/50">
                {{ __('public.plans.no_plans') }}
            </p>
        @else
            <div @class([
                'mt-16 grid grid-cols-1 gap-5',
                'lg:grid-cols-2' => $plans->count() === 2,
                'lg:grid-cols-3' => $plans->count() !== 2,
            ])>
                @foreach ($plans as $plan)
                    @php $highlight = $plan->id === $highlightPlanId; @endphp
                    <div
                        class="jf-reveal relative flex flex-col rounded-2xl border p-8 transition-all duration-200 {{ $highlight ? 'border-brand-500/40 bg-brand-500/5 jf-glow-accent' : 'border-zinc-200 bg-white hover:border-zinc-300 dark:border-white/8 dark:bg-surface-elevated dark:hover:border-white/15' }}"
                        style="transition-delay: {{ $loop->index * 80 }}ms">
                        @if ($highlight)
                            <span class="absolute -top-3 left-1/2 -translate-x-1/2">
                                <x-ui.badge color="brand">{{ __('public.plans.popular') }}</x-ui.badge>
                            </span>
                        @endif
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $plan->name }}</h3>
                        <div class="mt-5 flex items-baseline gap-1">
                            <span class="text-5xl font-extrabold tracking-tight text-zinc-900 dark:text-white">
                                {{ number_format((float) $plan->amount, 0, ',', '.') }}
                            </span>
                            <span class="text-sm font-medium text-zinc-500 dark:text-white/40">{{ __('public.ui.currency') }} {{ $planPeriod($plan) }}</span>
                        </div>
                        <ul class="mt-8 flex-1 space-y-3 text-sm text-zinc-600 dark:text-white/55">
                            @if (filled($plan->description))
                                <li class="flex items-start gap-2">
                                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-400" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M20 6 9 17l-5-5" />
                                    </svg>
                                    {{ $plan->description }}
                                </li>
                            @endif
                            <li class="flex items-start gap-2">
                                <svg class="mt-0.5 h-4 w-4 shrink-0 text-brand-400" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M20 6 9 17l-5-5" />
                                </svg>
                                {{ $plan->days }} {{ __('public.plans.days') }}
                            </li>
                        </ul>
                        <div class="mt-10">
                            <x-ui.button :href="$planSignupUrl($plan)" :variant="$highlight ? 'primary' : 'secondary'" size="lg"
                                class="w-full">
                                {{ __('public.plans.choose_named', ['name' => $plan->name]) }}
                            </x-ui.button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section id="contact" class="scroll-mt-24 px-4 pb-28 sm:px-6 lg:px-8">
        <div class="jf-reveal mx-auto max-w-5xl overflow-hidden rounded-3xl border border-zinc-200 bg-white px-6 py-20 text-center sm:px-14 dark:border-white/10 dark:bg-surface-elevated">
            <h2 class="text-4xl font-bold tracking-tight text-zinc-900 sm:text-5xl dark:text-white">{{ __('public.cta.title') }}</h2>
            <p class="mx-auto mt-5 max-w-lg text-lg text-zinc-600 dark:text-white/50">
                {{ __('public.cta.subtitle') }}
            </p>
            <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                <x-ui.button :href="$plansSectionHref" variant="primary" size="lg">
                    {{ __('public.plans.cta_now') }}
                </x-ui.button>
                <x-ui.button href="tel:+40000000000" variant="secondary" size="lg">
                    {{ __('public.cta.call_us') }}
                </x-ui.button>
            </div>
        </div>
    </section>
</x-layouts.public>
