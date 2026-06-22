@extends('member.layouts.app')

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ __('app.fitness.my_workout_plan') }}</h1>
            @if ($plan)
                <p class="mt-1 text-sm text-zinc-500">{{ $plan->name }} · {{ $plan->start_date->translatedFormat('d M Y') }}</p>
            @endif
        </div>
        <div class="flex gap-2">
            <a href="{{ route('member.fitness.nutrition-plan') }}" class="rounded-xl border px-4 py-2 text-sm font-semibold">{{ __('app.fitness.my_nutrition_plan') }}</a>
            <a href="{{ route('member.fitness.food-log') }}" class="rounded-xl border px-4 py-2 text-sm font-semibold">{{ __('app.fitness.food_log') }}</a>
        </div>
    </div>

    @if (! $plan)
        <p class="text-sm text-zinc-600">{{ __('app.fitness.no_workout_plan') }}</p>
    @else
        <div class="mb-8 grid gap-4 sm:grid-cols-7">
            @foreach ($plan->days as $day)
                <div @class([
                    'rounded-xl border p-3 text-center text-sm',
                    'border-brand-500 bg-brand-50 dark:bg-brand-500/10' => $day->id === $today?->id,
                    'border-zinc-200 dark:border-white/10' => $day->id !== $today?->id,
                ])>
                    <p class="font-semibold">{{ __('app.fitness.day') }} {{ $day->day_number }}</p>
                    <p class="text-xs text-zinc-500">{{ $day->name ?? '—' }}</p>
                </div>
            @endforeach
        </div>

        @if ($today)
            <section class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-white/10 dark:bg-zinc-950">
                <h2 class="text-lg font-semibold">{{ $today->name ?? __('app.fitness.todays_workout') }}</h2>
                <ul class="mt-4 space-y-4">
                    @foreach ($today->exercises as $item)
                        <li class="rounded-xl border border-zinc-100 p-4 dark:border-white/5">
                            <p class="font-medium">{{ $item->exercise?->name }}</p>
                            <p class="mt-1 text-sm text-zinc-500">
                                @if ($item->sets && $item->reps)
                                    {{ $item->sets }} × {{ $item->reps }} {{ __('app.fitness.reps') }}
                                @endif
                                @if ($item->duration_seconds)
                                    · {{ $item->duration_seconds }}s
                                @endif
                                @if ($item->rest_seconds)
                                    · {{ __('app.fitness.rest') }} {{ $item->rest_seconds }}s
                                @endif
                            </p>
                        </li>
                    @endforeach
                </ul>

                <form method="POST" action="{{ route('member.fitness.workout.log') }}" class="mt-6 space-y-3">
                    @csrf
                    <input type="hidden" name="plan_day_id" value="{{ $today->id }}">
                    <label class="block text-sm">
                        {{ __('app.fitness.duration_minutes') }}
                        <input type="number" name="duration_minutes" min="1" class="mt-1 w-full rounded-lg border px-3 py-2 dark:border-white/10 dark:bg-zinc-900">
                    </label>
                    <label class="block text-sm">
                        {{ __('app.fields.note') }}
                        <textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border px-3 py-2 dark:border-white/10 dark:bg-zinc-900"></textarea>
                    </label>
                    <button type="submit" class="rounded-xl bg-zinc-900 px-4 py-2 text-sm font-semibold text-white dark:bg-white dark:text-black">
                        {{ __('app.fitness.log_workout') }}
                    </button>
                </form>
            </section>
        @endif
    @endif
@endsection
