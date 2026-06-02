@props(['message'])

@if ($message)
    <p {{ $attributes->merge(['class' => 'mt-1 text-xs font-normal text-red-500 dark:text-red-400']) }}>
        {{ $message }}
    </p>
@endif
