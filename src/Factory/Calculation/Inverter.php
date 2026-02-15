<?php

namespace App\Factory\Calculation;

use App\Entity\Product\Panel;
use App\Repository\Product\InverterRepository;

class Inverter
{
    const LIMIT_RESIDENTIEL_NB_PANEL = 140;

    // limite de puissance en Wc pour utiliser des onduleurs résidentiels
    const POWER_LIMIT_RESIDENTIEL = 25000;
    const POWER_LIMIT_MONOPHASE = 6000;

    public function __construct(
        private readonly InverterRepository $inverterRepository,
        private float                       $lowerCost = PHP_FLOAT_MAX,
        private array                       $lowerAndBestCombo = [],
        private float                       $totalPower = 0,
        private int                         $qteInverter = 0
    )
    {
    }

    /**
     * Returns a list of Personality objects
     * @return \App\Entity\Product\Inverter[]
     */
    public function getRequiredInverter(
        float  $powerInstallation,
        string $typeInstallation
    ): array
    {
        $requiredInverters = [];
//        $powerInstallation /= 1.5;

        $powerInstallation *= 1000;

        $electricalPhase = $typeInstallation === 'I' || $powerInstallation >= static::POWER_LIMIT_MONOPHASE
            ? 'TP'
            : 'SP';

        $inverters = $this->inverterRepository->findAllByType($typeInstallation, $electricalPhase);

        if (count($inverters) <= 0) {
            return $requiredInverters;
        }

        $nbPowerestInverter = 0;

        // on optimise la boucle en calculant le nombre d'onduleurs le plus puissant si nécessaire
        if ($powerInstallation > $inverters[0]->getMaxPVPower()) {
            $nbPowerestInverter = intval(ceil($powerInstallation / ($inverters[0]->getMaxPVPower())));
            if ($nbPowerestInverter > 2) {
                $nbPowerestInverter -= 2;
                $powerInstallation -= $nbPowerestInverter * $inverters[0]->getMaxPVPower();
            } else {
                $nbPowerestInverter = 0;
            }
        }

        /* @var $inverter \App\Entity\Product\Inverter */
        foreach ($inverters as $key => $inverter) {
            $currentPrice = $inverter->getCurrentPrice(new \DateTimeImmutable());
//            $price = count($currentPrice) > 0 ? $currentPrice->first()->getPrice() :1;
            $price = 1;
            if ($electricalPhase === 'TP' && $inverter->getTypeInverter() !== 'H') {
                continue;
            }

            $requiredInverters[] = [
                'id' => $inverter->getId(),
                'name' => $inverter->getBrand() . ' ' . $inverter->getModel(),
                'maxPVPower' => $inverter->getMaxPVPower(),
                'power' => $inverter->getPower(),
                'price' => $price,
                'nbMax' => intval(ceil($powerInstallation / ($inverter->getMaxPVPower()))),
                'currentLoop' => 0,
                'preCalculationQte' => $key === 0 ? $nbPowerestInverter : 0,
                'preCalculationPrice' => $key === 0 ? $nbPowerestInverter * $price : 0,
            ];
        }
        static::loop($requiredInverters, $powerInstallation);

        $this->lowerCost = 0;
        $result = [];

        foreach ($this->lowerAndBestCombo as $inverter) {
            if ($inverter['currentLoop'] > 0 || $inverter['preCalculationQte'] > 0) {
                $result[] = [
                    'id' => $inverter['id'],
                    'name' => $inverter['name'],
                    'unitPrice' => $inverter['price'],
                    'nb' => $inverter['currentLoop'] + $inverter['preCalculationQte'],
                    'totalPrice' => $inverter['preCalculationPrice'] + ($inverter['price'] * $inverter['currentLoop']),
                    'power' => $inverter['power'],
                ];
                $this->qteInverter += $inverter['currentLoop'] + $inverter['preCalculationQte'];
                $this->lowerCost += $inverter['preCalculationPrice'] + ($inverter['price'] * $inverter['currentLoop']);
                $this->totalPower += ($inverter['currentLoop'] + $inverter['preCalculationQte']) * $inverter['power'];
            }
        }

        return [
            'qteInverter' => $this->qteInverter,
            'lowestCost' => $this->lowerCost,
            'totalPower' => $this->totalPower,
            'detailInverter' => $result
        ];
    }

    public function getInverterSizingCalculation(
        int                $nbPanels,
        Panel              $panel,
        int                $azimuthRatio,
        int                $temperatureMini,
        string             $electricalPhase,
        string             $inverterType,
        \DateTimeImmutable $dateOffer,
    ): array
    {
        $totalPower = $panel->getPower() * $nbPanels;
        $inverterTotalNb = 0;
        $inverterTotalPower = 0;
        $inverterTotalMPPT = 0;
        $totalCost = 0;

        $vocMax = static::getVocMax($panel->getVoltageOpenCircuit(), $panel->getCoefVoc(), $temperatureMini);
        $iscMax = static::getIscMax($panel->getShortCircuitCurrent(), $panel->getCoefIsc());

        if ($totalPower >= static::POWER_LIMIT_RESIDENTIEL) {
            $typeInstallation = 'I';
            $inverterType = null;
        } else {
            $typeInstallation = 'C';
        }

        // on cherche tous les onduleurs qui correspondent aux critères
        $inverterCriteria = [
            'type' => $typeInstallation,
            'electricalPhase' => $electricalPhase,
        ];

        if ($inverterType !== null) {
            $inverterCriteria['typeInverter'] = $inverterType;
        }

        $inverters = $this->inverterRepository->findBy(
            $inverterCriteria,
            ['power' => 'desc']
        );

        $kwaRequired = $totalPower / $azimuthRatio;

        $requiredInverters = [];

        foreach ($inverters as $inverter) {
            // on filtre par date de commercialisation (suivant les données dans inverter_price)
            $price = $inverter->getCurrentPrice($dateOffer);

            if ($price->first()) {
                $cablePrice = $inverter->getInverterCable()?->getCurrentPrice($dateOffer)?->first();
                $optionPrice = $inverter->getCurrentPriceOptions($dateOffer)?->first();
                $boxERPPrice = $inverter->getCurrentPriceProtectionBoxERP($dateOffer)?->first();
                $boxNonERPPrice = $inverter->getCurrentPriceProtectionBoxNonERP($dateOffer)?->first();

                $requiredInverters[] = [
                    'id' => $inverter->getId(),
                    'price' => $price->first()->getPrice(),
                    'brand' => $inverter->getBrand(),
                    'model' => $inverter->getModel(),
                    'power' => $inverter->getPower(),
                    'type' => $inverter->getTypeInverter(),
                    'cable_type' => $inverter->getInverterCable()?->getName() ?: null,
                    'cable_price' => $cablePrice ? $cablePrice->getPrice() : 0,
                    'options_price' => $optionPrice ? $optionPrice->getPrice() : 0,
                    'box_erp_price' => $boxERPPrice ? $boxERPPrice->getPrice() : 0,
                    'box_non_erp_price' => $boxNonERPPrice ? $boxNonERPPrice->getPrice() : 0,
                    'mppt' => [
                        'nb' => $inverter->getNbMppt(),
                        'nb_string' => $inverter->getNbStringPerMppt(),
                        'voltage_min' => $inverter->getMpptVoltageRangeMin(),
                        'voltage_max' => $inverter->getMpptVoltageRangeMax(),
                        'max_dc_input_current' => $inverter->getMaxDcInputCurrentMppt(),
                        'max_short_circuit_current' => $inverter->getMaxShortCircuitCurrentMppt()
                    ],
                    'max_input_power' => $inverter->getMaxInputPower(),
                    'max_dc_input_voltage' => $inverter->getMaxDcInputVoltage(),
                    'ratio' => 0,
                    'nb' => 0,
                ];
            }
        }

        $resultInverters = static::calculateBestInverterCombo($totalPower, $requiredInverters);

        foreach ($resultInverters as $inverter) {
            if ($inverter['nb'] > 0) {
                $inverterTotalNb += $inverter['nb'];
                $totalCost += $inverter['price'] * $inverter['nb'];
                $inverterTotalPower += ($inverter['power'] * 1000) * $inverter['nb'];
                $inverterTotalMPPT += $inverter['mppt']['nb'] * $inverter['nb'];
            }
        }

        // on vérifie que la puissance totale des panneaux ne dépasse pas la limite si monophasé
        $monophase_depassement = null;
        if (static::POWER_LIMIT_MONOPHASE < ($panel->getPower() * $nbPanels) && $electricalPhase === 'SP') {
            $monophase_depassement = [
                'limite_monophase' => static::POWER_LIMIT_MONOPHASE,
            ];
        }

        return [
            'puissance_totale' => $panel->getPower() * $nbPanels,
            'monophase_depassement' => $monophase_depassement,
            'inverters' => [
                'nb' => $inverterTotalNb,
                'total_cost' => $totalCost,
                'total_power' => $inverterTotalPower,
                'nb_total_mppt' => $inverterTotalMPPT,
                'detail' => $resultInverters,
            ],
            'vocMax' => $vocMax,
            'iscMax' => $iscMax,
        ];
    }

    private function getVocMax(float $voc, float $coefVoc, int $minTemp): float
    {
        return round($voc * (1 + ($coefVoc / 100) * ($minTemp - 25)), 2);
    }

    private function getIscMax(float $isc, float $coefIsc): float
    {
        return round($isc * (1 + ($coefIsc / 100) * (80 - 25)), 2);
    }

    private function loop(&$inverters, $powerNeeded, $index = 0): void
    {
        if ($index < count($inverters)) {
            for ($i = 0; $i <= $inverters[$index]['nbMax']; $i++) {
                $inverters[$index]['currentLoop'] = $i;
                static::loop($inverters, $powerNeeded, $index + 1);
            }
            $inverters[$index]['currentLoop'] = 0; // Remettre à zéro après la boucle
        } else {
            $currentCost = 0;
            $currentPower = 0;

            for ($o = 0; $o < count($inverters); $o++) {
                $currentCost += $inverters[$o]['price'] * $inverters[$o]['currentLoop'];
                $currentPower += $inverters[$o]['maxPVPower'] * $inverters[$o]['currentLoop'];
            }

            if ($currentPower >= intval($powerNeeded)) {
                if ($this->lowerCost > $currentCost) {
                    $this->lowerCost = $currentCost;
                    $this->lowerAndBestCombo = $inverters;
                }
            }
        }
    }

    public static function calculateBestInverterCombo($requiredPower, $inverters): array
    {
        // Convert the power to kilowatts
        $requiredPower = $requiredPower / 1000;

        // Initialize the best combination variables
        $bestCombo = [];
        $minInverterCount = INF;
        $minRatioDiff = INF;
        $currentCombo = [];
        $currentPower = 0;

        // Sort inverters by power in descending order
        usort($inverters, function ($a, $b) {
            return $b['power'] - $a['power'];
        });

        self::findCombo($inverters, $requiredPower, $currentCombo, $bestCombo, $minInverterCount, $minRatioDiff, $currentPower, 0);

        // Prepare the result
        $result = [];
        foreach ($bestCombo as $combo) {
            $model = $combo['model'];
            if (!isset($result[$model])) {
                $result[$model] = $combo;
                $result[$model]['nb'] = 1;
            } else {
                $result[$model]['nb']++;
            }
        }

        return array_values($result);
    }

    private static function findCombo($inverters, $requiredPower, &$currentCombo, &$bestCombo, &$minInverterCount, &$minRatioDiff, $currentPower, $index): void
    {
        if ($currentPower >= $requiredPower) {
            $currentRatio = $currentPower / $requiredPower;
            $ratioDiff = abs(1 - $currentRatio);
            $currentInverterCount = count($currentCombo);

            if ($currentInverterCount < $minInverterCount || ($currentInverterCount == $minInverterCount && $ratioDiff < $minRatioDiff)) {
                $minInverterCount = $currentInverterCount;
                $minRatioDiff = $ratioDiff;
                $bestCombo = $currentCombo;
            }
            return;
        }

        for ($i = $index; $i < count($inverters); $i++) {
            $inverter = $inverters[$i];

            // Check if adding this inverter exceeds the max input power
            if ($currentPower + $inverter['power'] > $inverter['max_input_power']) {
                continue;
            }

            $currentCombo[] = $inverter;
            self::findCombo($inverters, $requiredPower, $currentCombo, $bestCombo, $minInverterCount, $minRatioDiff, $currentPower + $inverter['power'], $i);
            array_pop($currentCombo);
        }
    }
}
