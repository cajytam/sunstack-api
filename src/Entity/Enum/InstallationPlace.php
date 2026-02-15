<?php

namespace App\Entity\Enum;

enum InstallationPlace: string
{
    case TOIT = 'T';
    case SOL = 'S';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::TOIT => 'Toit',
            self::SOL => 'Sol',
        };
    }
}
