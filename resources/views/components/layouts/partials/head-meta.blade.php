@props([
    'title' => null,
    'description' => null,
])

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ $title ?? config('app.name', 'Julius Fitness Gym') }}</title>

@if ($description)
    <meta name="description" content="{{ $description }}">
@endif

<meta name="generator" content="{{ config('studio.signature') }}">
<meta name="author" content="{{ config('studio.author') }}">
<meta name="studio" content="{{ config('studio.slug') }}">
<meta name="application-name" content="{{ config('studio.product') }}">

<meta name="theme-color" content="#0a0a0a" media="(prefers-color-scheme: dark)">
<meta name="theme-color" content="#f5f5f7" media="(prefers-color-scheme: light)">

<link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
<link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<x-layouts.partials.theme-script />

@stack('head')
