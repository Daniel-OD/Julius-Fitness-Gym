<?php

namespace App\Filament\Livewire;

use App\Filament\Support\DashboardQuickActions;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Hosts quick-action modals outside the dashboard page (avoids ?action= re-render races).
 */
class SidebarQuickActions extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    public function boot(): void
    {
        foreach (DashboardQuickActions::make($this) as $action) {
            $this->cacheAction($action->livewire($this));
        }
    }

    #[On('open-dashboard-quick-action')]
    public function openDashboardQuickAction(string $action): void
    {
        $this->mountAction($action);
    }

    public function render(): View
    {
        return view('filament.livewire.sidebar-quick-actions');
    }
}
