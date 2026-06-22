@extends('member.layouts.app')

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ __('app.classes.titles.weekly_schedule') }}</h1>
            <p class="mt-1 text-sm text-zinc-500">{{ $weekStart->translatedFormat('d M Y') }} – {{ $weekStart->copy()->addDays(6)->translatedFormat('d M Y') }}</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('member.classes.index', ['week' => $prevWeek]) }}"
               class="rounded-xl border border-zinc-200 px-3 py-2 text-sm font-medium transition hover:bg-zinc-100 dark:border-white/10 dark:hover:bg-white/5">
                ← {{ __('public.ui.prev') }}
            </a>
            <a href="{{ route('member.classes.index') }}"
               class="rounded-xl border border-zinc-200 px-3 py-2 text-sm font-medium transition hover:bg-zinc-100 dark:border-white/10 dark:hover:bg-white/5">
                {{ __('app.classes.actions.this_week') }}
            </a>
            <a href="{{ route('member.classes.index', ['week' => $nextWeek]) }}"
               class="rounded-xl border border-zinc-200 px-3 py-2 text-sm font-medium transition hover:bg-zinc-100 dark:border-white/10 dark:hover:bg-white/5">
                {{ __('public.ui.next') }} →
            </a>
            <a href="{{ route('member.classes.my-bookings') }}"
               class="rounded-xl bg-zinc-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-100">
                {{ __('app.classes.titles.my_bookings') }}
            </a>
        </div>
    </div>

    <div class="grid gap-6 sm:grid-cols-7">
        @foreach ($schedule as $dayIndex => $slots)
            @php
                $dayDate = $weekStart->copy()->addDays($dayIndex);
                $isToday = $dayDate->isToday();
                $isPast = $dayDate->isPast() && ! $isToday;
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
            <div @class(['rounded-2xl border p-3', $isToday ? 'border-brand-500 bg-brand-50 dark:bg-brand-500/10' : 'border-zinc-200 bg-white dark:border-white/10 dark:bg-zinc-950'])>
                <p @class(['mb-3 text-center text-xs font-semibold uppercase tracking-wider', $isToday ? 'text-brand-600 dark:text-brand-400' : 'text-zinc-500'])>
                    {{ $dayNames[$dayIndex] }}<br>
                    <span class="text-base font-bold {{ $isToday ? 'text-brand-600 dark:text-brand-400' : 'text-zinc-800 dark:text-zinc-200' }}">
                        {{ $dayDate->format('d') }}
                    </span>
                </p>

                @if ($slots->isEmpty())
                    <p class="text-center text-xs text-zinc-400">{{ __('app.classes.labels.no_classes') }}</p>
                @else
                    <div class="space-y-2">
                        @foreach ($slots as $slot)
                            @php
                                $scheduleId = $slot['schedule']->id;
                                $dateStr = $slot['date']->toDateString();
                                $available = $slot['available_slots'];
                                $alreadyBooked = isset($memberBookings[$dateStr]) && $memberBookings[$dateStr] == $scheduleId;
                            @endphp
                            <div class="rounded-xl border p-2 text-xs" style="border-left: 3px solid {{ $slot['schedule']->gymClass->color ?? '#6366f1' }}">
                                <p class="font-semibold text-zinc-800 dark:text-zinc-100">{{ $slot['schedule']->gymClass->name }}</p>
                                <p class="text-zinc-500">{{ substr($slot['schedule']->start_time, 0, 5) }}</p>
                                @if ($slot['schedule']->location)
                                    <p class="text-zinc-400">{{ $slot['schedule']->location }}</p>
                                @endif
                                @if ($slot['schedule']->gymClass->instructor)
                                    <p class="text-zinc-400">{{ $slot['schedule']->gymClass->instructor->name }}</p>
                                @endif
                                <p @class(['mt-1 font-medium', $available > 0 ? 'text-green-600' : 'text-red-500'])>
                                    {{ $available > 0 ? __('app.classes.labels.spots_left', ['count' => $available]) : __('app.classes.labels.full') }}
                                </p>

                                @if (! $isPast)
                                    @if ($alreadyBooked)
                                        <span class="mt-1 inline-block rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700">
                                            {{ __('app.classes.labels.booked') }}
                                        </span>
                                    @elseif ($available > 0)
                                        <form method="POST" action="{{ route('member.classes.book') }}" class="mt-1">
                                            @csrf
                                            <input type="hidden" name="schedule_id" value="{{ $scheduleId }}">
                                            <input type="hidden" name="date" value="{{ $dateStr }}">
                                            <button type="submit"
                                                    class="rounded-lg bg-zinc-900 px-3 py-1 text-xs font-semibold text-white transition hover:bg-zinc-700 dark:bg-white dark:text-zinc-900">
                                                {{ __('app.classes.actions.book') }}
                                            </button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endsection
