@extends('emails.invoices.layout')

@section('content')
    <div style="font-size: 14px; color: #111827;">
        {{ __('app.emails.greeting', ['name' => filled($memberName) ? $memberName : __('app.emails.there')]) }}
    </div>

    <div style="font-size: 14px; color: #111827; margin-top: 12px;">
        {!! __('app.emails.portal_invitation_line', ['gym' => e($gymName)]) !!}
    </div>

    <div style="margin-top: 24px; text-align: center;">
        <a href="{{ $setPasswordUrl }}"
            style="display: inline-block; background: #111827; color: #ffffff; font-size: 14px; font-weight: 600; text-decoration: none; padding: 12px 24px; border-radius: 8px;">
            {{ __('app.emails.portal_invitation_button') }}
        </a>
    </div>

    <div style="margin-top: 18px; font-size: 12px; color: #6b7280; word-break: break-all;">
        {{ $setPasswordUrl }}
    </div>

    <div style="margin-top: 18px; font-size: 12px; color: #6b7280;">
        {{ __('app.emails.reply_to_email') }}
    </div>
@endsection
