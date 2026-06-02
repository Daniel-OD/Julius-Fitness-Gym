@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'error' => null,
])

<div {{ $attributes->only('class') }}>
    @if ($label)
        <label @if ($name) for="{{ $name }}" @endif
            class="mb-1.5 block text-sm font-medium text-white/70">
            {{ $label }}
        </label>
    @endif

    <input type="{{ $type }}" @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
        {{ $attributes->except('class')->merge([
            'class' =>
                'jf-input ' .
                ($error
                    ? 'border-red-500/50 focus:border-red-500 focus:ring-red-500/30'
                    : ''),
        ]) }}>

    @if ($error)
        <p class="mt-1 text-xs text-red-400">{{ $error }}</p>
    @endif
</div>
