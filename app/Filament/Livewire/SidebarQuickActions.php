<?php

namespace App\Filament\Livewire;

use App\Filament\Support\DashboardQuickActions;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Contracts\View\View;
use Livewire\Component;

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

    /**
     * @return array<int, Action>
     */
    public function getSidebarActionsProperty(): array
    {
        return [
            $this->getAction('new_member'),
            $this->getAction('manual_checkin'),
            $this->getAction('new_lead'),
        ];
    }

    public function render(): View
    {
        return view('filament.livewire.sidebar-quick-actions');
    }
}
