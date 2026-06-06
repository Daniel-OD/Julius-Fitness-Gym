<button
    type="button"
    wire:click="toggle"
    wire:loading.attr="disabled"
    aria-label="{{ $enabled ? __('admin_guide.toggle_off') : __('admin_guide.toggle_on') }}"
    x-tooltip="{
        content: @js($enabled ? __('admin_guide.toggle_off') : __('admin_guide.toggle_on')),
        theme: $store.theme,
    }"
    @class([
        'fi-theme-switcher-btn',
        'fi-active' => $enabled,
    ])
>
    {{
        \Filament\Support\generate_icon_html(
            \Filament\Support\Icons\Heroicon::LightBulb,
            alias: \Filament\View\PanelsIconAlias::THEME_SWITCHER_SYSTEM_BUTTON,
        )
    }}
</button>
