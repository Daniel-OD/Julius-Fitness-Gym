{{ __('app.emails.greeting', ['name' => filled($recipientName) ? $recipientName : __('app.emails.there')]) }}

{{ strip_tags($introLine) }}

{{ __('app.emails.password_reset_password_label') }}: {{ $plainPassword }}

{{ $loginButtonLabel }}: {{ $loginUrl }}

{{ __('app.emails.password_reset_security_note') }}

{{ __('app.emails.reply_to_email') }}
