<x-filament-panels::page>
    <div class="max-w-md mx-auto">
        <p class="mb-6 text-sm text-zinc-600 dark:text-white/55">
            {{ __('app.auth.force_change_notice') }}
        </p>

        <form wire:submit.prevent="save" class="space-y-6">
            {{ $this->form }}
            <x-filament::button type="submit" wire:loading.class="opacity-50" class="w-full">
                {{ __('app.actions.save') }}
            </x-filament::button>
        </form>
    </div>
</x-filament-panels::page>
