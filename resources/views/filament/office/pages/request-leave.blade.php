<div>
    <form wire:submit="submit" class="space-y-6">
        {{ $this->form }}

        <x-filament::button type="submit">
            {{ __('app.hr.office.submit_leave') }}
        </x-filament::button>
    </form>
</div>
