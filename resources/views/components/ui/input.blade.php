@props([
    'label' => null,
    'name' => null,
    'type' => 'text',
    'error' => null,
])

<div {{ $attributes->only('class') }}>
    @if ($label)
        <label @if ($name) for="{{ $name }}" @endif
            class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}
        </label>
    @endif

    <input type="{{ $type }}" @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
        {{ $attributes->except('class')->merge([
            'class' =>
                'w-full rounded-lg border bg-white px-3 py-2 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-1 dark:bg-gray-800 dark:text-white ' .
                ($error
                    ? 'border-red-400 focus:border-red-500 focus:ring-red-500'
                    : 'border-gray-300 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700'),
        ]) }}>

    @if ($error)
        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $error }}</p>
    @endif
</div>
