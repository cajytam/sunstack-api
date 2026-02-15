<?php

namespace App\Utils;

use App\Repository\Simulation\SimulationRepository;
use Exception;

class Generator
{
    public static function generateRandomString($length = 20): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * @throws Exception
     */
    public static function getSimulationNumber(
        SimulationRepository $simulationRepository
    ): string
    {
        $count = ($simulationRepository->getByMonth(new \DateTime())) + 1;

        $number = str_pad($count, 4, '0', STR_PAD_LEFT);
        $currentDate = date('ym');
        return "$currentDate-$number";
    }

    /**
     * @throws Exception
     */
    public static function generateUniqueToken(): ?string
    {
        return bin2hex(random_bytes(32));
    }
}