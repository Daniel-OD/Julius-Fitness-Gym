<x-filament-panels::page>
    <form wire:submit="save" class="fi-fixed-positioning-context space-y-6">
        {{ $this->form }}

        <x-filament::actions
            :actions="$this->getFormActions()"
            alignment="end"
        />
    </form>
</x-filament-panels::page>
