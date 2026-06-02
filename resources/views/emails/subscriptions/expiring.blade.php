@php
    /** @var \App\Models\Subscription $subscription */
    /** @var int $daysLeft */
@endphp

@extends('emails.invoices.layout')

@section('content')
    <div style="font-size: 14px; color: #111827;">
        {{ __('app.emails.greeting', ['name' => filled($memberName) ? $memberName : __('app.emails.there')]) }}
    </div>

    <div style="font-size: 14px; color: #111827; margin-top: 12px;">
        @if ($daysLeft === 0)
            {!! __('app.emails.subscription_expired_line', [
                'plan' => e($subscription->plan?->name ?? '—'),
                'date' => e($subscription->end_date?->translatedFormat('d M Y') ?? '—'),
            ]) !!}
        @else
            {!! __('app.emails.subscription_expiring_line', [
                'plan' => e($subscription->plan?->name ?? '—'),
                'days' => $daysLeft,
                'date' => e($subscription->end_date?->translatedFormat('d M Y') ?? '—'),
            ]) !!}
        @endif
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
           style="margin-top: 18px; border-top: 1px solid #e5e7eb;">
        <tr>
            <td style="padding: 14px 0 0; font-size: 13px; color: #6b7280;">{{ __('app.fields.plan') }}</td>
            <td style="padding: 14px 0 0; font-size: 13px; color: #111827; font-weight: 700;" align="right">
                {{ $subscription->plan?->name ?? '—' }}
            </td>
        </tr>
        <tr>
            <td style="padding: 10px 0 0; font-size: 13px; color: #6b7280;">{{ __('app.fields.end_date') }}</td>
            <td style="padding: 10px 0 0; font-size: 13px; color: #111827; font-weight: 700;" align="right">
                {{ $subscription->end_date?->translatedFormat('d M Y') ?? '—' }}
            </td>
        </tr>
    </table>

    <div style="margin-top: 18px; font-size: 12px; color: #6b7280;">
        {{ __('app.emails.reply_to_email') }}
    </div>
@endsection
