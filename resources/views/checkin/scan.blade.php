<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} — Check-in</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f2f2f7; min-height: 100dvh; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .card { background: #fff; border-radius: 1.25rem; box-shadow: 0 2px 16px rgba(0,0,0,.08); max-width: 380px; width: 100%; padding: 2rem; text-align: center; }
        .icon { font-size: 3rem; margin-bottom: 1rem; }
        .status-success .icon::before { content: '✅'; }
        .status-warning .icon::before  { content: '⚠️'; }
        .status-error .icon::before    { content: '❌'; }
        .status-rate_limited .icon::before { content: '⏳'; }
        h1 { font-size: 1.25rem; font-weight: 700; color: #1c1c1e; margin-bottom: .5rem; }
        p  { color: #6b6b6b; font-size: .95rem; line-height: 1.5; }
        .meta { margin-top: 1.5rem; background: #f2f2f7; border-radius: .75rem; padding: 1rem; text-align: left; font-size: .875rem; }
        .meta dt { color: #8e8e93; font-size: .75rem; text-transform: uppercase; letter-spacing: .5px; }
        .meta dd { color: #1c1c1e; font-weight: 600; margin-bottom: .75rem; }
    </style>
</head>
<body>
<div class="card status-{{ $status }}">
    <div class="icon"></div>
    <h1>{{ $message }}</h1>

    @if ($member)
        <div class="meta">
            <dl>
                <dt>{{ __('app.fields.member') }}</dt>
                <dd>{{ $member->name }}</dd>
                @if ($subscription)
                    <dt>{{ __('app.fields.plan') }}</dt>
                    <dd>{{ $subscription->plan?->name }}</dd>
                    <dt>{{ __('app.fields.end_date') }}</dt>
                    <dd>{{ $subscription->end_date?->translatedFormat('d M Y') }}</dd>
                @endif
                @if ($checkIn)
                    <dt>{{ __('app.fields.date') }}</dt>
                    <dd>{{ $checkIn->checked_in_at?->translatedFormat('d M Y, H:i') }}</dd>
                @endif
            </dl>
        </div>
    @endif
</div>
</body>
</html>
