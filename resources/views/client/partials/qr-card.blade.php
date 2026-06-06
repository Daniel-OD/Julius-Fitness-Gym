<div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
    <p class="text-sm text-gray-500">{{ __('app.client_portal.qr_hint') }}</p>

    <div class="mx-auto mt-6 aspect-square w-full max-w-[220px] [&_svg]:h-full [&_svg]:w-full">
        {!! $qrSvg !!}
    </div>

    <div class="mt-6 text-center">
        <a href="{{ route('client.qr') }}"
            class="inline-flex items-center justify-center rounded-full bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-gray-800">
            {{ __('app.client_portal.open_qr') }}
        </a>
    </div>
</div>
