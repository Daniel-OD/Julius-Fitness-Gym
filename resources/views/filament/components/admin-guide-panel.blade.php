@php
    $guide = \App\Support\AdminGuide::forContext($guideKey ?? '');
@endphp

@if ($guide !== null && ($guide['title'] !== '' || $guide['summary'] !== '' || ($guide['steps'] ?? []) !== []))
    @include('filament.components.admin-guide-content', [
        'guide' => $guide,
        'contextKey' => $guideKey,
        'embedded' => true,
    ])
@endif
