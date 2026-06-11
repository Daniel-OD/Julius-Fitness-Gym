<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('app.reception.title') }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/reception-scan.js'])
</head>

<body class="h-full overflow-hidden bg-zinc-950 font-sans text-white antialiased">
    <div id="scanner-root" class="relative flex h-full flex-col" data-scan-url="{{ route('reception.scan.store') }}"
        data-camera-error="{{ __('app.reception.camera_error') }}">

        {{-- Camera feed --}}
        <video id="scanner-video" class="absolute inset-0 h-full w-full object-cover opacity-60" muted playsinline></video>
        <canvas id="scanner-canvas" class="hidden"></canvas>

        {{-- Full-screen result flash --}}
        <div id="result-overlay"
            class="pointer-events-none absolute inset-0 z-20 hidden items-center justify-center transition-opacity duration-200">
            <div class="flex flex-col items-center gap-4 px-6 text-center">
                <div id="result-icon" class="text-7xl"></div>
                <p id="result-member" class="text-4xl font-bold tracking-tight"></p>
                <p id="result-message" class="max-w-xl text-xl font-medium"></p>
                <p id="result-plan" class="text-base text-white/80"></p>
            </div>
        </div>

        {{-- Header --}}
        <header class="relative z-10 flex items-center justify-between gap-4 px-6 py-4">
            <div>
                <h1 class="text-lg font-semibold tracking-tight">{{ __('app.reception.title') }}</h1>
                <p class="text-sm text-zinc-400">{{ __('app.reception.subtitle') }}</p>
            </div>
            <a href="{{ url('/office') }}"
                class="rounded-full border border-white/20 px-4 py-2 text-sm font-medium text-zinc-300 transition-colors hover:bg-white/10">
                {{ __('app.reception.back') }}
            </a>
        </header>

        {{-- Scan frame --}}
        <main class="relative z-10 flex flex-1 items-center justify-center">
            <div class="flex flex-col items-center gap-6">
                <div class="h-64 w-64 rounded-3xl border-4 border-white/40 sm:h-80 sm:w-80"></div>
                <p id="scanner-status" class="text-sm font-medium text-zinc-300">{{ __('app.reception.scanning') }}</p>
            </div>
        </main>

        {{-- Manual fallback --}}
        <footer class="relative z-10 px-6 pb-6">
            <form id="manual-form" class="mx-auto flex w-full max-w-md items-center gap-2">
                <label for="manual-code" class="sr-only">{{ __('app.reception.manual_label') }}</label>
                <input id="manual-code" type="text" autocomplete="off"
                    placeholder="{{ __('app.reception.manual_placeholder') }}"
                    class="w-full rounded-full border border-white/20 bg-white/10 px-4 py-2.5 text-sm text-white placeholder-zinc-400 focus:border-white/50 focus:outline-none">
                <button type="submit"
                    class="shrink-0 rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-black transition-colors hover:bg-zinc-200">
                    {{ __('app.reception.submit') }}
                </button>
            </form>
        </footer>
    </div>
</body>

</html>
