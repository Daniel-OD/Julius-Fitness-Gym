@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'error' => null,
])

<div {{ $attributes->only('class') }}>
    @if ($label)
        <label @if ($name) for="{{ $name }}" @endif
            class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-white/70">
            {{ $label }}
        </label>
    @endif

    <input type="{{ $type }}" @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
        {{ $attributes->except('class')->merge([
            'class' =>
                'jf-input ' .
                ($error
                    ? 'border-red-500/60 focus:border-red-500 focus:ring-red-500/30 dark:border-red-500/50'
                    : ''),
        ]) }}>

    <x-ui.field-error :message="$error" />
</div>
