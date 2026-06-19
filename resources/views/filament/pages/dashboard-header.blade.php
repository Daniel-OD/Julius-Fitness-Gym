@php
$heading = $this->getHeading();
$breadcrumbs = filament()->hasBreadcrumbs() ? $this->getBreadcrumbs() : [];
$subheading = $this->getSubheading();
$period = (string) ($this->filters['period'] ?? '7days');
$headerActions = $this->getCachedHeaderActions();

$beforeActions = \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_HEADER_ACTIONS_BEFORE, scopes: $this->getRenderHookScopes());
$afterActions = \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::PAGE_HEADER_ACTIONS_AFTER, scopes: $this->getRenderHookScopes());
@endphp

<header @class(['fi-header', 'fi-header-has-breadcrumbs'=> $breadcrumbs])>
    <div>
        @if ($breadcrumbs)
        <x-filament::breadcrumbs :breadcrumbs="$breadcrumbs" />
        @endif

        @if (filled($heading))
        <h1 class="fi-header-heading">
            {{ $heading }}
        </h1>
        @endif

        @if (filled($subheading))
        <p class="fi-header-subheading">
            {{ $subheading }}
        </p>
        @endif
    </div>

    <div class="fi-header-actions-ctn" wire:init="ensureDefaultFilters">
        {{ $beforeActions }}

        @if ($headerActions)
            <x-filament::actions :actions="$headerActions" />
        @endif

        <div class="fi-ios-filter-bar flex w-full flex-wrap items-stretch justify-end gap-2 px-2 py-2 sm:items-center sm:gap-3 sm:px-4">
            {{ $this->form }}

            @if ($period === 'custom')
            <x-filament::button color="gray" outlined size="sm" wire:click="applyCustomRangeFromFilters">
                {{ __('app.dashboard.actions.apply') }}
            </x-filament::button>
            @endif
        </div>

        {{ $afterActions }}
    </div>
</header>
