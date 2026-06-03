<?php

namespace App\Filament\Widgets\Office;

use Filament\Widgets\Widget;

/**
 * Reserved space for the live “present now” widget (implemented separately).
 */
class OfficePresentNowPlaceholderWidget extends Widget
{
    protected static ?int $sort = -60;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'filament.office.widgets.present-now-placeholder';

    protected static bool $isLazy = false;
}
