<?php

namespace App\Entity\Traits;

trait CivilitiesTrait
{
    private const GENDER_FEMALE = 'F';
    private const GENDER_MALE = 'M';

    public static function getAvailableCivilities(): array
    {
        return [
            'Madame' => self::GENDER_FEMALE,
            'Monsieur' => self::GENDER_MALE,
        ];
    }

    public static function getCivilityText(string $gender): array
    {
        return \array_flip(self::getAvailableCivilities()[$gender]);
    }
}
