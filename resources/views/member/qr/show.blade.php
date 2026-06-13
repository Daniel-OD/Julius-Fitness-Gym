@php
    use App\Helpers\Helpers;
    $settings = Helpers::getSettings();
    $gymName = data_get($settings, 'general.gym_name') ?: config('app.name');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <title>{{ __('app.members.qr.title') }} · {{ $gymName }}</title>
    @vite(['resources/css/app.css'])
</head>

<body class="flex min-h-dvh flex-col items-center justify-center bg-black text-white" id="qr-body">

    {{-- Back button --}}
    <a href="{{ route('member.dashboard') }}"
        class="safe-top fixed left-4 top-4 z-10 flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-2 text-sm font-medium text-white backdrop-blur transition hover:bg-white/20"
        style="top: max(1rem, env(safe-area-inset-top, 1rem))">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        {{ __('app.member_portal.back_to_dashboard') }}
    </a>

    {{-- QR code --}}
    <div class="flex w-full flex-col items-center px-8"
        style="padding-top: max(4rem, env(safe-area-inset-top, 4rem)); padding-bottom: max(2rem, env(safe-area-inset-bottom, 2rem))">

        <div class="w-full max-w-xs rounded-3xl bg-white p-5 shadow-2xl [&_svg]:h-full [&_svg]:w-full">
            {!! $qrSvg !!}
        </div>

        <p class="mt-5 text-lg font-semibold tracking-widest text-white/90">{{ $member->code }}</p>
        <p class="mt-1 text-sm text-white/50">{{ $member->name }}</p>

        <p class="mt-6 max-w-[18rem] text-center text-xs leading-relaxed text-white/40">
            {{ __('app.member_portal.qr_scan_hint') }}
        </p>
    </div>

    <script>
        // Keep screen awake while on this page (Wake Lock API)
        if ('wakeLock' in navigator) {
            navigator.wakeLock.request('screen').catch(() => {});
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') {
                    navigator.wakeLock.request('screen').catch(() => {});
                }
            });
        }
    </script>
</body>

</html>
