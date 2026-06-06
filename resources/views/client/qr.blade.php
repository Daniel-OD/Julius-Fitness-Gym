<x-layouts.minimal :title="$member->name . ' · QR'">
    <div class="flex flex-1 flex-col items-center text-center">
        <h1 class="text-2xl font-semibold tracking-tight">{{ $member->name }}</h1>
        <p class="mt-1 text-sm text-zinc-500">{{ $member->code }}</p>

        <div class="mt-10 w-full max-w-xs rounded-3xl border border-zinc-200 bg-white p-6 shadow-sm">
            <div class="mx-auto aspect-square w-full max-w-[260px] [&_svg]:h-full [&_svg]:w-full">
                {!! $qrSvg !!}
            </div>
        </div>

        <div class="mt-8">
            @include('client.partials.subscription-badge', ['access' => $access])
        </div>

        <p class="mt-6 text-sm text-zinc-500">{{ __('app.client_portal.qr_hint') }}</p>

        <a href="{{ route('client.dashboard') }}"
            class="mt-10 text-sm font-medium text-zinc-500 transition-colors hover:text-zinc-900">
            ← {{ __('app.client_portal.title') }}
        </a>
    </div>
</x-layouts.minimal>
