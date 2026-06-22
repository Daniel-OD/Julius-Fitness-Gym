@extends('member.layouts.app')

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold">{{ __('app.classes.titles.my_bookings') }}</h1>
            <p class="mt-1 text-sm text-zinc-500">{{ __('app.classes.labels.upcoming_only') }}</p>
        </div>
        <a href="{{ route('member.classes.index') }}"
           class="rounded-xl border border-zinc-200 px-4 py-2 text-sm font-medium transition hover:bg-zinc-100 dark:border-white/10 dark:hover:bg-white/5">
            ← {{ __('app.classes.titles.weekly_schedule') }}
        </a>
    </div>

    @if ($bookings->isEmpty())
        <div class="rounded-2xl border border-dashed border-zinc-300 py-16 text-center dark:border-white/10">
            <p class="text-sm text-zinc-500">{{ __('app.classes.labels.no_upcoming_bookings') }}</p>
            <a href="{{ route('member.classes.index') }}"
               class="mt-4 inline-block rounded-xl bg-zinc-900 px-5 py-2.5 text-sm font-semibold text-white dark:bg-white dark:text-zinc-900">
                {{ __('app.classes.actions.browse_classes') }}
            </a>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($bookings as $booking)
                <div class="flex items-center justify-between rounded-2xl border border-zinc-200 bg-white px-5 py-4 dark:border-white/10 dark:bg-zinc-950"
                     style="border-left: 4px solid {{ $booking->schedule->gymClass->color ?? '#6366f1' }}">
                    <div>
                        <p class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $booking->schedule->gymClass->name }}</p>
                        <p class="mt-0.5 text-sm text-zinc-500">
                            {{ $booking->booked_date->translatedFormat('l, d M Y') }}
                            · {{ substr($booking->schedule->start_time, 0, 5) }}
                            @if ($booking->schedule->location)
                                · {{ $booking->schedule->location }}
                            @endif
                        </p>
                        @if ($booking->schedule->gymClass->instructor)
                            <p class="mt-0.5 text-xs text-zinc-400">{{ $booking->schedule->gymClass->instructor->name }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-3">
                        <span @class(['rounded-full px-2.5 py-1 text-xs font-semibold',
                            'bg-blue-100 text-blue-700' => $booking->status->value === 'booked',
                            'bg-green-100 text-green-700' => $booking->status->value === 'attended',
                        ])>{{ $booking->status->getLabel() }}</span>
                        @if ($booking->status->value === 'booked')
                            <form method="POST" action="{{ route('member.classes.cancel', $booking) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('{{ __('app.classes.labels.cancel_confirm') }}')"
                                        class="rounded-xl border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50">
                                    {{ __('app.actions.cancel') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
