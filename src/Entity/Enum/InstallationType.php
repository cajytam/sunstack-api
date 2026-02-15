<?php

namespace App\Entity\Enum;

enum InstallationType: string
{
    case INDUSTRIEL = 'I';
    case COMMERCIAL = 'C';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::INDUSTRIEL => 'Industriel',
            self::COMMERCIAL => 'Commercial',
        };
    }
}
