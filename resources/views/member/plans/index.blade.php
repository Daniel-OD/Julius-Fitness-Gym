<x-layouts.minimal :title="__('app.member.plans.title')">
    <div class="mb-8 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                {{ __('app.member.plans.title') }}
            </h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('app.member.plans.subtitle') }}
            </p>
        </div>
        <form method="POST" action="{{ route('member.logout') }}">
            @csrf
            <x-ui.button type="submit" variant="ghost" size="sm">
                {{ __('app.member.auth.logout') }}
            </x-ui.button>
        </form>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800/40 dark:bg-green-900/20 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @if ($plans->isEmpty())
        <div class="rounded-2xl border border-zinc-200 bg-zinc-50 p-10 text-center dark:border-white/8 dark:bg-white/2">
            <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('app.member.plans.no_plans') }}</p>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($plans as $plan)
                <article class="flex flex-col rounded-2xl border border-zinc-200 bg-white p-5 dark:border-white/8 dark:bg-white/3">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                        {{ $plan->name }}
                    </h2>

                    @if (filled($plan->description))
                        <p class="mt-2 flex-1 text-sm leading-relaxed text-zinc-500 dark:text-zinc-400">
                            {{ $plan->description }}
                        </p>
                    @endif

                    <dl class="mt-4 space-y-1 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('app.fields.amount') }}</dt>
                            <dd class="font-semibold text-zinc-900 dark:text-white">
                                {{ number_format((float) $plan->amount, 2) }} lei
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('app.fields.duration') }}</dt>
                            <dd class="font-medium text-zinc-900 dark:text-white">
                                {{ $plan->days }} {{ __('app.member.plans.days') }}
                            </dd>
                        </div>
                    </dl>

                    <form method="POST" action="{{ route('member.plans.select', $plan) }}" class="mt-5">
                        @csrf
                        <x-ui.button type="submit" variant="primary" size="md" class="w-full">
                            {{ __('app.member.plans.choose_plan') }}
                        </x-ui.button>
                    </form>
                </article>
            @endforeach
        </div>
    @endif
</x-layouts.minimal>
