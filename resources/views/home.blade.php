@php
    // Placeholder content — swap for real Plan models once the backend exposes them.
    $features = [
        [
            'title' => 'Strength & free weights',
            'desc' => 'Fully-equipped strength floor with racks, platforms and a complete free-weight range.',
            'icon' => '<path d="M14.4 14.4 9.6 9.6" /><path d="M18.657 21.485a2 2 0 1 1-2.829-2.828l-1.767 1.768a2 2 0 1 1-2.829-2.829l6.364-6.364a2 2 0 1 1 2.829 2.829l-1.768 1.767a2 2 0 1 1 2.828 2.829z" /><path d="m21.5 21.5-1.4-1.4" /><path d="M3.9 3.9 2.5 2.5" /><path d="M6.404 12.768a2 2 0 1 1-2.829-2.829l1.768-1.767a2 2 0 1 1-2.828-2.829l2.828-2.828a2 2 0 1 1 2.829 2.828l1.767-1.768a2 2 0 1 1 2.829 2.829z" />',
        ],
        [
            'title' => 'Group classes',
            'desc' => 'HIIT, spinning, yoga and functional training led by certified coaches every day.',
            'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />',
        ],
        [
            'title' => 'Personal training',
            'desc' => 'One-on-one coaching with tailored programs to hit your goals faster.',
            'icon' => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" /><path d="m9 12 2 2 4-4" />',
        ],
        [
            'title' => 'Open 7 days',
            'desc' => 'Early mornings to late nights — train on your schedule, every day of the week.',
            'icon' => '<path d="M3 12a9 9 0 1 0 18 0 9 9 0 0 0-18 0Z" /><path d="M12 7v5l3 3" />',
        ],
    ];

    $plans = [
        [
            'name' => 'Day Pass',
            'price' => '50',
            'period' => 'per visit',
            'highlight' => false,
            'features' => ['Full gym access for a day', 'Locker & showers', 'No commitment'],
        ],
        [
            'name' => 'Monthly',
            'price' => '150',
            'period' => 'per month',
            'highlight' => true,
            'features' => ['Unlimited gym access', 'All group classes', 'Locker & showers', '1 guest pass / month'],
        ],
        [
            'name' => 'Annual',
            'price' => '1,200',
            'period' => 'per year',
            'highlight' => false,
            'features' => ['Everything in Monthly', '2 months free', '2 PT sessions', 'Priority class booking'],
        ],
    ];

    $stats = [
        ['value' => '1,200+', 'label' => 'Active members'],
        ['value' => '40+', 'label' => 'Weekly classes'],
        ['value' => '15', 'label' => 'Expert coaches'],
        ['value' => '24/7', 'label' => 'App access'],
    ];
@endphp

<x-layouts.public title="Julius Fitness Gym — Train hard, get results">
    {{-- Hero --}}
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 -z-10 bg-gradient-to-br from-brand-50 via-white to-white dark:from-brand-600/10 dark:via-gray-950 dark:to-gray-950"></div>
        <div class="mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <div class="grid items-center gap-12 lg:grid-cols-2">
                <div>
                    <x-ui.badge color="brand">New members welcome</x-ui.badge>
                    <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl lg:text-6xl dark:text-white">
                        Train hard.<br>
                        <span class="text-brand-600 dark:text-brand-400">Get real results.</span>
                    </h1>
                    <p class="mt-6 max-w-lg text-lg text-gray-600 dark:text-gray-300">
                        Julius Fitness is your home for strength, conditioning and community. Modern equipment,
                        expert coaches and classes for every level.
                    </p>
                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <x-ui.button href="#plans" variant="primary" size="lg">Become a member</x-ui.button>
                        <x-ui.button href="#features" variant="secondary" size="lg">Explore the gym</x-ui.button>
                    </div>
                </div>

                <div class="relative">
                    <div class="aspect-[4/3] overflow-hidden rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 shadow-xl">
                        <div class="flex h-full w-full items-center justify-center">
                            <svg class="h-32 w-32 text-white/30" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14.4 14.4 9.6 9.6" /><path d="M18.657 21.485a2 2 0 1 1-2.829-2.828l-1.767 1.768a2 2 0 1 1-2.829-2.829l6.364-6.364a2 2 0 1 1 2.829 2.829l-1.768 1.767a2 2 0 1 1 2.828 2.829z" /><path d="m21.5 21.5-1.4-1.4" /><path d="M3.9 3.9 2.5 2.5" /><path d="M6.404 12.768a2 2 0 1 1-2.829-2.829l1.768-1.767a2 2 0 1 1-2.828-2.829l2.828-2.828a2 2 0 1 1 2.829 2.828l1.767-1.768a2 2 0 1 1 2.829 2.829z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stats strip --}}
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

    {{-- Features --}}
    <section id="features" class="mx-auto max-w-7xl scroll-mt-20 px-4 py-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-white">
                Everything you need to train
            </h2>
            <p class="mt-4 text-gray-600 dark:text-gray-300">
                One membership, full access to equipment, classes and coaching.
            </p>
        </div>

        <div class="mt-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($features as $feature)
                <x-ui.card>
                    <span class="flex h-11 w-11 items-center justify-center rounded-lg bg-brand-50 text-brand-600 dark:bg-brand-600/15 dark:text-brand-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            {!! $feature['icon'] !!}
                        </svg>
                    </span>
                    <h3 class="mt-4 font-semibold text-gray-900 dark:text-white">{{ $feature['title'] }}</h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ $feature['desc'] }}</p>
                </x-ui.card>
            @endforeach
        </div>
    </section>

    {{-- Plans --}}
    <section id="plans" class="scroll-mt-20 bg-gray-50 py-20 dark:bg-gray-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-white">
                    Simple, honest pricing
                </h2>
                <p class="mt-4 text-gray-600 dark:text-gray-300">Pick the plan that fits your routine. Cancel anytime.</p>
            </div>

            <div class="mt-12 grid grid-cols-1 gap-6 lg:grid-cols-3">
                @foreach ($plans as $plan)
                    <div class="relative flex flex-col rounded-2xl border bg-white p-6 shadow-sm dark:bg-gray-950 {{ $plan['highlight'] ? 'border-brand-500 ring-1 ring-brand-500' : 'border-gray-200 dark:border-gray-800' }}">
                        @if ($plan['highlight'])
                            <span class="absolute -top-3 left-1/2 -translate-x-1/2">
                                <x-ui.badge color="brand">Most popular</x-ui.badge>
                            </span>
                        @endif
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $plan['name'] }}</h3>
                        <div class="mt-4 flex items-baseline gap-1">
                            <span class="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white">{{ $plan['price'] }}</span>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">lei {{ $plan['period'] }}</span>
                        </div>
                        <ul class="mt-6 space-y-3 text-sm">
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
                            <x-ui.button href="#contact" :variant="$plan['highlight'] ? 'primary' : 'secondary'" size="lg"
                                class="w-full">
                                Choose {{ $plan['name'] }}
                            </x-ui.button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- About --}}
    <section id="about" class="mx-auto max-w-7xl scroll-mt-20 px-4 py-20 sm:px-6 lg:px-8">
        <div class="grid items-center gap-12 lg:grid-cols-2">
            <div>
                <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl dark:text-white">
                    More than a gym
                </h2>
                <p class="mt-4 text-gray-600 dark:text-gray-300">
                    Julius Fitness was built by people who train. We keep our equipment modern, our floors clean,
                    and our community welcoming — whether it's your first session or your thousandth.
                </p>
                <ul class="mt-6 space-y-3">
                    @foreach (['Certified, friendly coaching staff', 'Spacious, well-maintained facilities', 'A community that keeps you accountable'] as $point)
                        <li class="flex items-start gap-3 text-gray-700 dark:text-gray-300">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-brand-600 dark:text-brand-400" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M21.801 10A10 10 0 1 1 17 3.335" /><path d="m9 11 3 3L22 4" />
                            </svg>
                            {{ $point }}
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="aspect-[4/3] overflow-hidden rounded-2xl bg-gradient-to-br from-gray-800 to-gray-950 shadow-xl">
                <div class="flex h-full w-full items-center justify-center">
                    <svg class="h-28 w-28 text-white/20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" />
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />
                    </svg>
                </div>
            </div>
        </div>
    </section>

    {{-- Contact CTA --}}
    <section id="contact" class="scroll-mt-20 px-4 pb-20 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-5xl overflow-hidden rounded-3xl bg-brand-600 px-6 py-14 text-center shadow-xl sm:px-12">
            <h2 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Ready to start?</h2>
            <p class="mx-auto mt-4 max-w-xl text-brand-50">
                Drop by for a tour or sign up online. Your first session is on us.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a href="#plans"
                    class="inline-flex items-center justify-center rounded-lg bg-white px-6 py-2.5 text-sm font-semibold text-brand-700 transition-colors hover:bg-brand-50">
                    View plans
                </a>
                <a href="tel:+40000000000"
                    class="inline-flex items-center justify-center rounded-lg border border-white/30 px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-white/10">
                    Call us
                </a>
            </div>
        </div>
    </section>
</x-layouts.public>
