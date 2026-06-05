<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="generator" content="{{ config('studio.signature') }}">
        <meta name="author" content="{{ config('studio.author') }}">
        <meta name="studio" content="{{ config('studio.slug') }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="jf-min-h-screen font-sans text-gray-900 antialiased">
        <div class="jf-auth-shell jf-safe-x jf-safe-b flex min-h-screen flex-col items-center justify-center bg-gray-100 pt-6 sm:pt-0">
            <div>
                <a href="/">
                    <x-application-logo class="w-20 h-20 fill-current text-gray-500" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
                {{ $slot }}
            </div>

            <x-studio.signature variant="login" class="mt-6" />
        </div>
        <x-studio.html-comment />
    </body>
</html>
