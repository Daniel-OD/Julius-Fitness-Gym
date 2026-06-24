@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];
@endphp

<div
    x-data="{
        show: @js($show),
        focusables() {
            // All focusable element types...
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)]
                // All non-disabled elements...
                .filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) -1 },
    }"
    x-init="$watch('show', value => {
        if (value) {
            document.body.classList.add('overflow-y-hidden');
            {{ $attributes->has('focusable') ? 'setTimeout(() => firstFocusable().focus(), 100)' : '' }}
        } else {
            document.body.classList.remove('overflow-y-hidden');
        }
    })"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    x-show="show"
    class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
    style="display: {{ $show ? 'block' : 'none' }};"
>
    <div
        x-show="show"
        class="fixed inset-0 transform transition-all"
        x-on:click="show = false"
        x-transition:enter="ease-[cubic-bezier(0.16,1,0.3,1)] duration-[380ms]"
        x-transition:enter-start="opacity-0 backdrop-blur-0"
        x-transition:enter-end="opacity-100 backdrop-blur-md"
        x-transition:leave="ease-[cubic-bezier(0.4,0,0.2,1)] duration-[260ms]"
        x-transition:leave-start="opacity-100 backdrop-blur-md"
        x-transition:leave-end="opacity-0 backdrop-blur-0"
    >
        <div class="absolute inset-0 bg-gray-950/45 dark:bg-gray-950/65 backdrop-blur-md"></div>
    </div>

    <div
        x-show="show"
        class="mb-6 overflow-hidden rounded-2xl border border-gray-200/80 bg-white/95 shadow-2xl backdrop-blur-xl transform transition-all dark:border-white/10 dark:bg-gray-900/95 sm:w-full {{ $maxWidth }} sm:mx-auto"
        x-transition:enter="ease-[cubic-bezier(0.16,1,0.3,1)] duration-[420ms]"
        x-transition:enter-start="opacity-0 translate-y-5 scale-[0.94]"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="ease-[cubic-bezier(0.4,0,0.2,1)] duration-[280ms]"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-3 scale-[0.97]"
    >
        {{ $slot }}
    </div>
</div>
