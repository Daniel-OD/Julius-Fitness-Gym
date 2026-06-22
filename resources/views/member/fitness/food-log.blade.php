@extends('member.layouts.app')

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <div class="mb-8">
        <h1 class="text-2xl font-semibold">{{ __('app.fitness.food_log') }}</h1>
        <p class="mt-1 text-sm text-zinc-500">{{ $date->translatedFormat('d M Y') }}</p>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-4">
        <div class="rounded-xl border p-4"><p class="text-xs text-zinc-500">{{ __('app.fitness.daily_calories') }}</p><p class="text-xl font-bold">{{ $totals['calories'] }} @if($plan)/ {{ $plan->daily_calories }} @endif</p></div>
        <div class="rounded-xl border p-4"><p class="text-xs text-zinc-500">{{ __('app.fitness.protein_g') }}</p><p class="text-xl font-bold">{{ $totals['protein'] }}g @if($plan)/ {{ $plan->protein_g }}g @endif</p></div>
        <div class="rounded-xl border p-4"><p class="text-xs text-zinc-500">{{ __('app.fitness.carbs_g') }}</p><p class="text-xl font-bold">{{ $totals['carbs'] }}g @if($plan)/ {{ $plan->carbs_g }}g @endif</p></div>
        <div class="rounded-xl border p-4"><p class="text-xs text-zinc-500">{{ __('app.fitness.fat_g') }}</p><p class="text-xl font-bold">{{ $totals['fat'] }}g @if($plan)/ {{ $plan->fat_g }}g @endif</p></div>
    </div>

    <form method="POST" action="{{ route('member.fitness.food-log.store') }}" class="mb-8 grid gap-3 rounded-2xl border p-5 sm:grid-cols-2">
        @csrf
        <input type="date" name="logged_at" value="{{ $date->toDateString() }}" class="rounded-lg border px-3 py-2 dark:border-white/10 dark:bg-zinc-900">
        <select name="meal_type" class="rounded-lg border px-3 py-2 dark:border-white/10 dark:bg-zinc-900" required>
            @foreach (\App\Enums\MealType::cases() as $mealType)
                <option value="{{ $mealType->value }}">{{ $mealType->getLabel() }}</option>
            @endforeach
        </select>
        <select name="food_item_id" class="rounded-lg border px-3 py-2 dark:border-white/10 dark:bg-zinc-900 sm:col-span-2" required>
            @foreach ($foods as $food)
                <option value="{{ $food->id }}">{{ $food->name }}</option>
            @endforeach
        </select>
        <input type="number" name="quantity" value="100" min="1" step="0.1" class="rounded-lg border px-3 py-2 dark:border-white/10 dark:bg-zinc-900" required>
        <select name="unit" class="rounded-lg border px-3 py-2 dark:border-white/10 dark:bg-zinc-900">
            <option value="g">g</option>
            <option value="serving">{{ __('app.fitness.serving') }}</option>
        </select>
        <button type="submit" class="rounded-xl bg-zinc-900 px-4 py-2 text-sm font-semibold text-white sm:col-span-2 dark:bg-white dark:text-black">{{ __('app.fitness.add_food_log') }}</button>
    </form>

    @if ($logs->isEmpty())
        <p class="text-sm text-zinc-600">{{ __('app.fitness.no_food_logs') }}</p>
    @else
        <ul class="divide-y rounded-2xl border dark:divide-white/5">
            @foreach ($logs as $log)
                <li class="flex justify-between px-4 py-3 text-sm">
                    <span>{{ $log->meal_type?->getLabel() }} · {{ $log->foodItem?->name }} ({{ $log->quantity }}{{ $log->unit }})</span>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
