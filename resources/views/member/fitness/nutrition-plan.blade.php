@extends('member.layouts.app')

@section('content')
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ __('app.fitness.my_nutrition_plan') }}</h1>
            @if ($plan)
                <p class="mt-1 text-sm text-zinc-500">{{ $plan->name }}</p>
            @endif
        </div>
        <a href="{{ route('member.fitness.workout-plan') }}" class="rounded-xl border px-4 py-2 text-sm font-semibold">{{ __('app.fitness.my_workout_plan') }}</a>
    </div>

    @if (! $plan)
        <p class="text-sm text-zinc-600">{{ __('app.fitness.no_nutrition_plan') }}</p>
    @else
        <div class="mb-6 grid gap-4 sm:grid-cols-4">
            <div class="rounded-xl border p-4"><p class="text-xs text-zinc-500">{{ __('app.fitness.daily_calories') }}</p><p class="text-xl font-bold">{{ $plan->daily_calories }}</p></div>
            <div class="rounded-xl border p-4"><p class="text-xs text-zinc-500">{{ __('app.fitness.protein_g') }}</p><p class="text-xl font-bold">{{ $plan->protein_g }}g</p></div>
            <div class="rounded-xl border p-4"><p class="text-xs text-zinc-500">{{ __('app.fitness.carbs_g') }}</p><p class="text-xl font-bold">{{ $plan->carbs_g }}g</p></div>
            <div class="rounded-xl border p-4"><p class="text-xs text-zinc-500">{{ __('app.fitness.fat_g') }}</p><p class="text-xl font-bold">{{ $plan->fat_g }}g</p></div>
        </div>

        @foreach ($plan->meals as $meal)
            <section class="mb-4 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-white/10 dark:bg-zinc-950">
                <h2 class="font-semibold">{{ $meal->meal_type?->getLabel() }} @if($meal->name) · {{ $meal->name }} @endif</h2>
                <ul class="mt-3 divide-y text-sm dark:divide-white/5">
                    @foreach ($meal->items as $item)
                        <li class="flex justify-between py-2">
                            <span>{{ $item->foodItem?->name }} · {{ $item->quantity }}{{ $item->unit }}</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach
    @endif
@endsection
