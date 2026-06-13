@php
    use App\Enums\Status;
    use App\Helpers\Helpers;

    $badgeClasses = fn (?string $tone): string => match ($tone) {
        'green' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400',
        'orange' => 'bg-orange-500/10 text-orange-700 dark:text-orange-400',
        'red' => 'bg-red-500/10 text-red-700 dark:text-red-400',
        default => 'bg-zinc-500/10 text-zinc-600 dark:text-zinc-400',
    };

    $invoiceIsPaid = fn ($invoice): bool => $invoice->status === Status::Paid
        || (float) ($invoice->due_amount ?? 0) <= 0;
@endphp

@extends('member.layouts.app')

@section('content')
    @if (session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800/40 dark:bg-green-900/20 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @if ($pendingPaymentSubscription?->plan)
        <div class="mb-6 rounded-2xl border border-brand-200 bg-brand-50 px-5 py-4 dark:border-brand-500/20 dark:bg-brand-500/10">
            <h2 class="text-sm font-semibold text-brand-900 dark:text-brand-100">
                {{ __('app.member.plans.pending_payment_title') }}
            </h2>
            <p class="mt-2 text-sm text-brand-800 dark:text-brand-200">
                {{ __('app.member.plans.pending_payment_plan', ['plan' => $pendingPaymentSubscription->plan->name]) }}
            </p>
            <p class="mt-2 text-sm text-brand-700/90 dark:text-brand-200/90">
                {{ __('app.member.plans.pay_at_reception') }}
            </p>
        </div>
    @endif

    <div class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight">
            {{ __('app.member_portal.welcome', ['name' => $member->name]) }}
        </h1>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
            {{ __('app.member_portal.dashboard_hint') }}
        </p>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- CARD 1: Active subscription --}}
        <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-950">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                {{ __('app.member_portal.active_subscription') }}
            </h2>

            @if ($activeSubscription?->plan)
                <div class="mt-4 space-y-3">
                    <p class="text-xl font-semibold tracking-tight">{{ $activeSubscription->plan->name }}</p>

                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('app.fields.end_date') }}</dt>
                            <dd class="font-medium">
                                {{ $activeSubscription->end_date?->translatedFormat('d M Y') }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-zinc-500 dark:text-zinc-400">{{ __('app.widgets.days_left') }}</dt>
                            <dd class="font-medium">{{ $daysRemaining }}</dd>
                        </div>
                    </dl>

                    <p class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $badgeClasses($subscriptionBadgeTone) }}">
                        @if ($daysRemaining > 7)
                            {{ __('app.member_portal.subscription_badge_ok', ['days' => $daysRemaining]) }}
                        @elseif ($daysRemaining >= 3)
                            {{ __('app.member_portal.subscription_badge_soon', ['days' => $daysRemaining]) }}
                        @else
                            {{ __('app.member_portal.subscription_badge_urgent', ['days' => $daysRemaining]) }}
                        @endif
                    </p>
                </div>
            @else
                <p class="mt-4 text-sm text-zinc-600 dark:text-zinc-300">
                    {{ __('app.member_portal.no_active_subscription') }}
                </p>
            @endif
        </section>

        {{-- CARD 2: QR code --}}
        <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-950">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                {{ __('app.members.qr.title') }}
            </h2>

            <div id="member-qr-print-area" class="mt-4 flex flex-col items-center text-center">
                <div class="w-full max-w-[200px] rounded-2xl border border-zinc-100 bg-white p-4 dark:border-white/10 dark:bg-zinc-900 [&_svg]:h-full [&_svg]:w-full">
                    {!! $qrSvg !!}
                </div>
                <p class="mt-3 text-xs text-zinc-500 dark:text-zinc-400">{{ $member->code }}</p>
            </div>

            <div class="mt-5 flex flex-col gap-2">
                <a href="{{ route('member.qr.show') }}"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-zinc-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-zinc-800 dark:bg-white dark:text-black dark:hover:bg-zinc-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H4a1 1 0 01-1-1v-4zM14 4a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V4zM14 15a1 1 0 00-1 1v4a1 1 0 001 1h4a1 1 0 001-1v-4a1 1 0 00-1-1h-4z" />
                    </svg>
                    {{ __('app.member_portal.show_qr') }}
                </a>
                <div class="flex gap-2">
                    <a href="{{ route('member.qr.download') }}"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-zinc-200 px-4 py-2.5 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50 dark:border-white/15 dark:text-white dark:hover:bg-white/5">
                        {{ __('app.members.qr.download') }}
                    </a>
                    <button type="button" onclick="window.print()"
                        class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl border border-zinc-200 px-4 py-2.5 text-sm font-semibold text-zinc-900 transition hover:bg-zinc-50 dark:border-white/15 dark:text-white dark:hover:bg-white/5">
                        {{ __('app.members.qr.print') }}
                    </button>
                </div>
            </div>
        </section>

        {{-- CARD 3: Invoices --}}
        <section class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-zinc-950 lg:col-span-3">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                {{ __('app.resources.invoices.plural') }}
            </h2>

            @if ($invoices->isEmpty())
                <p class="mt-4 text-sm text-zinc-600 dark:text-zinc-300">
                    {{ __('app.member_portal.no_invoices') }}
                </p>
            @else
                <div class="mt-4 overflow-x-auto">
                    <table class="w-full min-w-[32rem] text-left text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 text-xs uppercase tracking-wider text-zinc-500 dark:border-white/10 dark:text-zinc-400">
                                <th class="pb-3 pr-4 font-semibold">#</th>
                                <th class="pb-3 pr-4 font-semibold">{{ __('app.fields.date') }}</th>
                                <th class="pb-3 pr-4 font-semibold">{{ __('app.fields.total') }}</th>
                                <th class="pb-3 pr-4 font-semibold">{{ __('app.fields.status') }}</th>
                                <th class="pb-3 font-semibold"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-white/5">
                            @foreach ($invoices as $invoice)
                                @php $paid = $invoiceIsPaid($invoice); @endphp
                                <tr>
                                    <td class="py-3 pr-4 font-medium">{{ $invoice->number ?? '—' }}</td>
                                    <td class="py-3 pr-4 text-zinc-600 dark:text-zinc-300">
                                        {{ $invoice->date?->translatedFormat('d M Y') ?? '—' }}
                                    </td>
                                    <td class="py-3 pr-4 font-medium">
                                        {{ Helpers::formatCurrency((float) ($invoice->total_amount ?? 0)) }}
                                    </td>
                                    <td class="py-3 pr-4">
                                        <span @class([
                                            'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                            'bg-emerald-500/10 text-emerald-700 dark:text-emerald-400' => $paid,
                                            'bg-red-500/10 text-red-700 dark:text-red-400' => ! $paid,
                                        ])>
                                            {{ $paid ? __('app.fields.paid') : __('app.member_portal.unpaid') }}
                                        </span>
                                    </td>
                                    <td class="py-3 text-right">
                                        <a href="{{ route('member.invoices.pdf', $invoice) }}"
                                            class="text-sm font-medium text-zinc-900 underline-offset-2 hover:underline dark:text-white">
                                            {{ __('app.member_portal.download_pdf') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>

    @push('head')
        <style>
            @media print {
                body * {
                    visibility: hidden;
                }

                #member-qr-print-area,
                #member-qr-print-area * {
                    visibility: visible;
                }

                #member-qr-print-area {
                    position: fixed;
                    inset: 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 2rem;
                }
            }
        </style>
    @endpush
@endsection
