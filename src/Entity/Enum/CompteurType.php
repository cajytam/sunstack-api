<?php

namespace App\Entity\Enum;

enum CompteurType: string
{
    case MONOPHASE = 'M';
    case TRIPHASE = 'T';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::MONOPHASE => 'Monophasé',
            self::TRIPHASE => 'Triphasé',
        };
    }
}
