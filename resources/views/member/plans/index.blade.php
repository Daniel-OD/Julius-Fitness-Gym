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
        <div class="grid gap-4">
            @foreach ($plans as $plan)
                <div class="rounded-2xl border border-zinc-200 bg-white p-6 dark:border-white/8 dark:bg-white/3">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-base font-semibold text-zinc-900 dark:text-white">
                                {{ $plan->name }}
                            </h2>
                            @if (filled($plan->description))
                                <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ $plan->description }}</p>
                            @endif
                            <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $plan->days }} {{ __('app.member.plans.days') }}
                                &middot;
                                <span class="font-semibold text-zinc-900 dark:text-white">
                                    {{ number_format((float) $plan->amount, 2) }} lei
                                </span>
                            </p>
                        </div>
                        <form method="POST" action="{{ route('member.plans.store') }}" class="shrink-0">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                            <x-ui.button type="submit" variant="primary" size="sm">
                                {{ __('app.member.plans.choose') }}
                            </x-ui.button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.minimal>
