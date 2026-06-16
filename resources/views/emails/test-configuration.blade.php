<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('app.settings.mail.test_subject', ['gym' => $gymName]) }}</title>
</head>
<body style="font-family: ui-sans-serif, system-ui, sans-serif; line-height: 1.5; color: #18181b;">
    <p>{{ __('app.settings.mail.test_body', ['gym' => $gymName]) }}</p>
</body>
</html>
