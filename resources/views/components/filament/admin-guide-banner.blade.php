@php
    $guide = \App\Support\AdminGuide::forCurrentPage();
@endphp

@if ($guide !== null && ($guide['title'] !== '' || $guide['summary'] !== '' || ($guide['steps'] ?? []) !== []))
    @include('filament.components.admin-guide-content', [
        'guide' => $guide,
        'contextKey' => \App\Support\AdminGuide::resolveKey(request()->route()?->getName()) ?? 'page',
        'embedded' => false,
    ])
@endif
