@php
    $statusClasses = match ($access->tone) {
        'active' => 'bg-emerald-500/10 text-emerald-700',
        'expired' => 'bg-orange-500/10 text-orange-700',
        default => 'bg-gray-500/10 text-gray-600',
    };
@endphp

<p class="inline-flex items-center rounded-full px-4 py-2 text-sm font-medium {{ $statusClasses }}">
    @if ($access->isActive)
        <span class="mr-2 h-2 w-2 rounded-full bg-emerald-500"></span>
    @elseif ($access->tone === 'expired')
        <span class="mr-2 h-2 w-2 rounded-full bg-orange-500"></span>
    @endif
    {{ $access->label }}
</p>
