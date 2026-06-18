@extends('emails.invoices.layout')

@section('content')
    <div style="font-size: 14px; color: #111827;">
        {{ __('app.emails.greeting', ['name' => filled($recipientName) ? $recipientName : __('app.emails.there')]) }}
    </div>

    <div style="font-size: 14px; color: #111827; margin-top: 12px;">
        {!! $introLine !!}
    </div>

    <div style="margin-top: 20px; padding: 16px; border: 1px solid #e5e7eb; border-radius: 8px; background: #f9fafb;">
        <div style="font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em;">
            {{ __('app.emails.password_reset_password_label') }}
        </div>
        <div style="margin-top: 8px; font-size: 18px; font-weight: 700; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; color: #111827;">
            {{ $plainPassword }}
        </div>
    </div>

    <div style="margin-top: 24px; text-align: center;">
        <a href="{{ $loginUrl }}"
            style="display: inline-block; background: #111827; color: #ffffff; font-size: 14px; font-weight: 600; text-decoration: none; padding: 12px 24px; border-radius: 8px;">
            {{ $loginButtonLabel }}
        </a>
    </div>

    <div style="margin-top: 18px; font-size: 12px; color: #6b7280; word-break: break-all;">
        {{ $loginUrl }}
    </div>

    <div style="margin-top: 18px; font-size: 12px; color: #6b7280;">
        {{ __('app.emails.password_reset_security_note') }}
    </div>

    <div style="margin-top: 18px; font-size: 12px; color: #6b7280;">
        {{ __('app.emails.reply_to_email') }}
    </div>
@endsection
