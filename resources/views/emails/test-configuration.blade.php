@extends('emails.invoices.layout')

@section('content')
    <p style="font-family: ui-sans-serif, system-ui, sans-serif; font-size: 14px; color: #18181b; margin: 0;">
        {{ __('app.settings.mail.test_body', ['gym' => $gymName]) }}
    </p>
@endsection
