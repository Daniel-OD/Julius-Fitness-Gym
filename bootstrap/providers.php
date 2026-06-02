<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\OfficePanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    OfficePanelProvider::class,
];
