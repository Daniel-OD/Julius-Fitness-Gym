<div class="jf-sidebar-quick-actions-slot">
    <div class="fi-sidebar-group jf-sidebar-quick-actions">
        <div class="fi-sidebar-group-label">
            {{ __('app.navigation.quick_actions') }}
        </div>

        <ul class="fi-sidebar-group-items">
            @foreach ($this->sidebarActions as $action)
                <li class="fi-sidebar-item jf-sidebar-quick-action">
                    {{ $action }}
                </li>
            @endforeach
        </ul>
    </div>

    <x-filament-actions::modals />
</div>
