<x-filament-widgets::widget class="office-present-now">
    <x-filament::section
        :heading="__('app.office.present_now')"
        :description="__('app.office.present_now_placeholder')"
        icon="heroicon-o-user-group"
        icon-color="primary"
        class="office-present-now__section"
    >
        <div class="office-present-now__body">
            <div class="office-present-now__icon" aria-hidden="true">
                <x-filament::icon
                    icon="heroicon-o-users"
                    class="h-12 w-12 text-primary-500"
                />
            </div>
            <p class="office-present-now__hint">
                {{ __('app.office.present_now_hint') }}
            </p>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
