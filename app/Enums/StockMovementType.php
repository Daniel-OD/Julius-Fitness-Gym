<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum StockMovementType: string implements HasColor, HasLabel
{
    case In = 'in';
    case Out = 'out';
    case Adjustment = 'adjustment';

    public function getLabel(): string
    {
        $key = 'app.shop.stock_movement_types.'.$this->value;
        $translated = __($key);

        return $translated !== $key ? $translated : $this->name;
    }

    public function getColor(): string
    {
        return match ($this) {
            self::In => 'success',
            self::Out => 'danger',
            self::Adjustment => 'warning',
        };
    }
}
