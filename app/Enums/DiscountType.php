<?php

namespace App\Enums;

enum DiscountType: string
{
    case Percentage = 'percentage';
    case Fixed = 'fixed';
    case TwoForOne = 'two_for_one';

    public function label(): string
    {
        return match ($this) {
            self::Percentage => 'Porcentaje',
            self::Fixed => 'Monto fijo',
            self::TwoForOne => '2 × 1',
        };
    }
}
