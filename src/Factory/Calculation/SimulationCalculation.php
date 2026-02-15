<?php

namespace App\Factory\Calculation;

use App\Entity\Product\Battery;
use App\Entity\Product\ChargingPoint;
use App\Entity\Product\Panel;
use App\Entity\Product\PricingFactorPrice;
use App\Repository\Product\PricingFactorPriceRepository;
use DateTimeImmutable;
use http\Exception\InvalidArgumentException;

readonly class SimulationCalculation
{
    const SUNSTACK_MARKED_ITEM = [
        'panel',
        'inverter',
        'inverter_cable',
        'inverter_option',
        'inverter_option',
        'inverter_protection_box',
        'surveyMainBuilding',
        'lift',
        'maison_mere_margin',
        'other',
    ];

    const PANEL_BY_DAY = 22;

    public function __construct(
        private int                          $panelNb,
        private Panel                        $panel,
        private DateTimeImmutable            $dateOffer,
        private int                          $inverterNb,
        private float                        $inverterTotalCost,
        private PricingFactorPriceRepository $factorPriceRepository,
        private array                        $installationParams,
        private string                       $marginSunstack = "E",
        private string                       $marginMaisonMere = "E",
        private int|null                     $batteryNb = 0,
        private Battery|null                 $battery = null,
        private int|null                     $chargingPointNb = 0,
        private ChargingPoint|null           $chargingPoint = null,
        private bool                         $surveyMain = false,
        private int                          $surveyOtherBuilding = 0,
        private int                          $nbCharpentesNonVisibles = 0,
        private int                          $nbCharpentesBeton = 0,
        private bool                         $onlyBySimulation = false
    ) {}

    public function getPriceDetail(): array
    {
        $panelPrice = static::getPanelPrice();

        if (!$panelPrice) {
            return [
                "error" => "Aucun prix trouvé pour les panneaux " . $this->panel->getFullname() . " en date du " . $this->dateOffer->format('d-m-Y')
            ];
        }

        $prices = [
            'panel' => ($this->onlyBySimulation ? 0 : $panelPrice),
            'inverter' => ($this->onlyBySimulation ? 0 : static::getInverterPrice()),
            'inverter_cable' => ($this->onlyBySimulation ? 0 : static::getInverterCablePrice()),
            'inverter_option' => ($this->onlyBySimulation ? 0 : static::getInverterOptionsPrice()),
            'inverter_protection_box' => ($this->onlyBySimulation ? 0 : static::getInverterProtectionBoxPrice()),
            'other' => static::mainPricingFactors(),
            'maison_mere_margin' => ($this->onlyBySimulation ? 0 : static::getMaisonMereMargin()),
            'surveyMainBuilding' => (!$this->onlyBySimulation ? 0 : static::getMainSurveyBuilding()),
            'surveyOtherBuilding' => (!$this->onlyBySimulation ? 0 : static::getSurveyOtherBuilding()),
            'surveyCharpentesNonVisibles' => (!$this->onlyBySimulation ? 0 : static::getSurveyInvisibleCharpente()),
            'surveyCharpentesBeton' => (!$this->onlyBySimulation ? 0 : static::getSurveyConcreteCharpente()),
            'eco_participation' => (!$this->onlyBySimulation ? 0 : .5 * $this->panelNb),
            'lift' => (!$this->onlyBySimulation || $this->dateOffer >= (new \DateTimeImmutable())->setDate(2024, 7, 30)->setTime(0, 0) ? 0 : static::getLiftPrice()),
            'inverters_warranty' => ($this->onlyBySimulation ? 0 : static::getInverterPrice() * 1.5),
        ];

        $prices['unmarginable_prices'] = static::getUnmarginablePrice($prices); // /!\ ordre important car sinon sunstack_margin_base sera pris en compte
        $prices['sunstack_margin_base'] = static::getsunstackMarginBase($prices);

        $prices['sunstack_margin_marked'] = static::getMarkedPrice($prices['sunstack_margin_base'], $this->marginSunstack);

        $prices['batteries_price'] = 0;

        if ($this->batteryNb > 0) {
            $basePrice = static::getBatteryPrice();
            $prices['batteries_price'] = static::getMarkedPrice($basePrice, 'B');
        }

        $prices['total_price'] = round(
            $prices['sunstack_margin_marked']
                + $prices['unmarginable_prices']
                + $prices['batteries_price'],
            2
        );

        return $prices;
    }

    private function getPanelPrice(): float|null
    {
        $prices = $this->panel->getCurrentPrice($this->dateOffer);

        if (count($prices)) {
            $pricePerPanel = $prices->first()->getPrice();
            return $this->panelNb * $pricePerPanel;
        }

        return null;
    }

    private function getBatteryPrice(): float|null
    {
        if (!$this->battery) return null;
        $prices = $this->battery->getCurrentPrice($this->dateOffer);

        if (count($prices)) {
            $pricePerBattery = $prices->first()->getPrice();
            return $this->batteryNb * $pricePerBattery;
        }

        return null;
    }

    private function getMainSurveyBuilding(): float
    {
        if ($this->surveyMain) {
            return 3650;
        }
        return 0;
    }

    private function getSurveyOtherBuilding(): float
    {
        return $this->surveyOtherBuilding * 1100;
    }

    private function getSurveyInvisibleCharpente(): float
    {
        return $this->nbCharpentesNonVisibles * 1400;
    }

    private function getSurveyConcreteCharpente(): float
    {
        return $this->nbCharpentesBeton * 2300;
    }

    private function getInverterCablePrice(): float
    {
        $totalPrice = 0;
        $biggestInverter = true;

        if (isset($this->installationParams['inverter_detail']['inverters']['detail'])) {
            foreach ($this->installationParams['inverter_detail']['inverters']['detail'] as $inverter) {
                if (isset($inverter['cable_price'], $inverter['nb'])) {
                    if ($biggestInverter && $inverter['nb'] > 1) {
                        // Set 15 for the first inverter
                        $nbMeter = 15;
                        $totalPrice += $inverter['cable_price'] * $nbMeter;

                        // Set 5 for the remaining inverters
                        $remainingNb = $inverter['nb'] - 1;
                        $nbMeter = 5;
                        $totalPrice += $inverter['cable_price'] * $nbMeter * $remainingNb;
                    } else {
                        // Default case: set 5 for all inverters
                        $nbMeter = $biggestInverter ? 15 : 5;
                        $totalPrice += $inverter['cable_price'] * $nbMeter * $inverter['nb'];
                    }
                    $biggestInverter = false;
                }
            }
        }

        return $totalPrice;
    }

    private function getInverterOptionsPrice(): float
    {
        $totalPrice = 0;

        if (isset($this->installationParams['inverter_detail']['inverters']['detail'])) {
            foreach ($this->installationParams['inverter_detail']['inverters']['detail'] as $inverter) {
                if (isset($inverter['options_price'], $inverter['nb'])) {
                    $totalPrice += $inverter['options_price'] * $inverter['nb'];
                }
            }
        }

        return $totalPrice;
    }

    private function getInverterProtectionBoxPrice(): float
    {
        $totalPrice = 0;
        $keyProtectionBox = 'box_non_erp_price';

        if (array_key_exists('is_building_erp', $this->installationParams)) {
            if ($this->installationParams['is_building_erp'] === true) {
                $keyProtectionBox = 'box_erp_price';
            }
        }

        if (isset($this->installationParams['inverter_detail']['inverters']['detail'])) {
            foreach ($this->installationParams['inverter_detail']['inverters']['detail'] as $inverter) {
                if (isset($inverter[$keyProtectionBox], $inverter['nb'])) {
                    $totalPrice += $inverter[$keyProtectionBox] * $inverter['nb'];
                }
            }
        }

        return $totalPrice;
    }

    private function getSunstackMarginBase(array $prices): float
    {
        $amount = 0;
        foreach ($prices as $k => $v) {
            if (in_array($k, static::SUNSTACK_MARKED_ITEM)) {
                // si $v est un array, c'est qu'il s'agit d'un autre facteur de prix
                // donc ne prendre que la valeur "marked"
                if (is_array($v)) {
                    foreach ($v as $item) {
                        $amount += $item['marked'];
                    }
                } else {
                    $amount += $v;
                }
            }
        }
        return $amount;
    }

    private function getUnmarginablePrice(array $prices): float
    {
        $amount = 0;
        foreach ($prices as $k => $v) {
            // si $k === 'other' c'est qu'il s'agit des autres facteurs, il ne faut prendre que les unmarked
            if (!in_array($k, static::SUNSTACK_MARKED_ITEM) || $k === 'other') {
                // si $v est un array, c'est qu'il s'agit d'un autre facteur de prix
                // donc ne pas prendre que la valeur "unmarked"
                if (is_array($v)) {
                    foreach ($v as $item) {
                        $amount += $item['unmarked'];
                    }
                } else {
                    $amount += $v;
                }
            }
        }
        return $amount;
    }

    private function getMaisonMereMarginBase(int $panelNb, float $panelPower): float
    {
        return $panelNb * $panelPower;
    }

    private function getMaisonMereMargin(): float
    {
        return static::getMaisonMereMarginBase($this->panelNb, $this->panel->getPower()) * static::getCoefficientmarginMaisonMere($this->marginMaisonMere);
    }

    private function getInverterPrice(): float
    {
        return $this->inverterTotalCost;
    }

    public function getMarkedPrice(float $price, string $margin = null, float $coefficient = null, string $marginType = 'sunstack'): float
    {
        if (!in_array($marginType, ['maison_mere', 'sunstack'], true)) {
            throw new \Exception("Invalid marginType. Allowed values are 'maison_mere' or 'sunstack'.");
        }

        if (!$coefficient && !$margin) return 0;

        if ($margin !== null) {
            if ($marginType === 'sunstack') {
                $coefficient = static::getCoefficientmarginSunstack($margin);
            } else {
                $coefficient = static::getCoefficientmarginMaisonMere($margin);
            }
        }
        return $price / (1 - $coefficient);
    }

    public function getCoefficientmarginSunstack(string $margin): float
    {
        $marginCoefficient = [
            "E" => .5,
            "D" => .45,
            "C" => .42,
            "B" => .38,
            "A" => .34,
        ];

        return $marginCoefficient[$margin];
    }

    public function getCoefficientmarginMaisonMere(string $margin): float
    {
        $marginCoefficient = [
            "E" => .1,
            "D" => .08,
            "C" => .07,
            "B" => .06,
            "A" => .05,
        ];

        return $marginCoefficient[$margin];
    }

    private function mainPricingFactors(): array
    {
        $listPricingFactors = static::generatePricingFactors($this->dateOffer);

        $satisfiedPricingFactors = static::getSatisfiedConditionFactorPrices($listPricingFactors, $this->installationParams);

        return static::calculatePricesFromListPricingFactors($satisfiedPricingFactors);
    }

    /**
     * @param DateTimeImmutable $dateOffer
     * @return array
     * Fonction qui génère le tableau des facteurs de prix trouvés pour la date de l'offre
     */
    private function generatePricingFactors(DateTimeImmutable $dateOffer): array
    {
        $query = $this->factorPriceRepository->getActivePricingFactor($dateOffer);

        $data = [];

        /* @var PricingFactorPrice $factor */
        foreach ($query as $factor) {
            $conditions = $factor->getPricingFactorConditions();
            $dataFactor = [
                'marked' => $factor->getMarkedPrice(),
                'unmarked' => $factor->getUnmarkedPrice(),
            ];

            // gestion des conditions
            $conditionsArray = [];
            foreach ($conditions as $condition) {
                $conditionsArray[$condition->getType()][] = [
                    'limitType' => $condition->getLimitType(),
                    'value' => $condition->getValue()
                ];
            }
            $dataFactor['conditions'] = $conditionsArray;

            $data[$factor->getPricingFactor()->getName()][] = $dataFactor;
        }

        return $data;
    }

    /**
     * @param array $listPricingFactors
     * @param array $simulationParameters
     * @return array
     * Permet de checker si les conditions sont remplies pour les facteurs de prix
     * Seules les conditions "by" ne sont pas checker mais automatiquement retournées
     */
    private function getSatisfiedConditionFactorPrices(
        array $listPricingFactors,
        array $simulationParameters
    ): array {
        $data = [];

        foreach ($listPricingFactors as $name => $pricingFactor) {
            // on trie par sous array par "from" pour bien avoir du plus grand au plus petit
            usort($pricingFactor, function ($a, $b) {
                $valueA = null;
                $valueB = null;

                // Extract the 'from' value from $a
                foreach ($a['conditions'] as $conditionType => $conditions) {
                    foreach ($conditions as $condition) {
                        if ($condition['limitType'] === 'from') {
                            $valueA = (int)$condition['value'];
                            break 2;
                        }
                    }
                }

                // Extract the 'from' value from $b
                foreach ($b['conditions'] as $conditionType => $conditions) {
                    foreach ($conditions as $condition) {
                        if ($condition['limitType'] === 'from') {
                            $valueB = (int)$condition['value'];
                            break 2;
                        }
                    }
                }

                // Compare the extracted 'from' values
                return $valueA <=> $valueB;
            });

            foreach ($pricingFactor as $factor) {
                $conditionSatisfied = 0;
                $conditionTotal = 0;

                foreach ($factor['conditions'] as $condition => $params) {
                    foreach ($params as $param) {
                        switch ($param['limitType']) {
                            case 'from':
                                $conditionTotal++;
                                if (intval($simulationParameters[$condition]) >= intval($param['value'])) {
                                    $conditionSatisfied++;
                                }
                                break;
                            case 'by':
                                $conditionTotal++;
                                if ($simulationParameters[$condition] !== null && $simulationParameters[$condition] > 0) {
                                    $conditionSatisfied++;
                                }
                                break;
                            case 'bool':
                                $conditionTotal++;
                                if ($simulationParameters[$condition] === true) {
                                    $conditionSatisfied++;
                                }
                                break;
                            case null:
                                $conditionTotal++;
                                strval($simulationParameters[$condition]) !== $param['value'] ?: $conditionSatisfied++;
                                break;
                        }
                    }
                }

                //si condition remplie, on ajoute à $data
                if ($conditionTotal === $conditionSatisfied) {
                    if ($this->onlyBySimulation) {
                        if (array_key_exists('installation', $factor['conditions'])) {
                            $data[$name] = $factor;
                        }
                    } else {
                        $data[$name] = $factor;
                    }
                }
            }
        }

        return $data;
    }

    private function calculatePricesFromListPricingFactors(array $satisfiedPricingFactors): array
    {
        $data = [];

        foreach ($satisfiedPricingFactors as $factor => $params) {
            $coefficient = static::getCoefficientFromConditions($params['conditions'], $factor);

            if (is_array($coefficient)) {
                $onceBySimulation = true;
                $coefficient = $coefficient['onceBySimulation'];
            } else {
                $onceBySimulation = false;
            }

            $data[$factor] = [
                'marked' => ($params['marked'] ?? 0) * $coefficient,
                'unmarked' => ($params['unmarked'] ?? 0) * $coefficient,
                'onceBySimulation' => $onceBySimulation
            ];
        }
        return $data;
    }

    private function getCoefficientFromConditions(array $coefficientConditions, string $factorName): float|array
    {
        $elementNb = 0;

        ksort($coefficientConditions);

        $conditionEverDone = null;

        foreach ($coefficientConditions as $condition => $params) {
            $limitBy = null;
            $limitFrom = null;
            $limitFromType = null;
            $limiByType = null;

            if ($condition === 'installation') {
                if (!($factorName === 'Prix de pose' && array_key_exists('wp_total', $coefficientConditions))) {
                    return ['onceBySimulation' => $params[0]['value']];
                }
            }

            // on trie le tableau des conditions pour avoir un ordre par limite
            static::sortSubarraysByLimitType($params);

            foreach ($params as $param) {
                if ($param['limitType'] === 'by') {
                    $limitBy = $param['value'];
                    $limiByType = $condition;
                } elseif ($param['limitType'] === 'from') {
                    $limitFrom = $param['value'];
                    $limitFromType = $condition;
                }
            }

            if ($limitFrom && $limiByType === $condition) {
                //$elementNb = ($this->installationParams[$condition] - $limitFrom);
                $elementNb = $this->installationParams[$condition];
            }

            if ($limitBy) {
                if ($limitFrom && $limitFromType === $condition && $limiByType === $condition) {
                    $elementNb = ceil($elementNb / $limitBy);
                } else {
                    if ($limitFromType === null) {
                        $elementNb = floor($this->installationParams[$condition] / $limitBy);
                    } else if ($limiByType !== $condition) {
                        $elementNb = $this->installationParams[$condition];
                    }
                }
            }

            if (!$limitBy && !$limitFrom && !$conditionEverDone) {
                $elementNb = 1;
            } else {
                $conditionEverDone = true;
            }
        }

        return $elementNb;
    }

    /**
     * Permet de trier les conditions par type de limite alphabétique (standardise le traitement)
     * @param array $array
     * @return void
     */
    private function sortSubarraysByLimitType(array &$array): void
    {
        usort($array, [$this, 'sortByLimitType']);
    }

    /**
     * Compare les valeurs pour retourner en ordre croissant ou alphabétique
     * @param $a
     * @param $b
     * @return int
     */
    private function sortByLimitType($a, $b): int
    {
        return strcmp($a['limitType'], $b['limitType']);
    }

    private function getLiftPrice(): float
    {
        $prices = [
            1 => 100,
            2 => 78,
            5 => 66,
            21 => 56,
            42 => 54
        ];

        if ($this->installationParams['location_type'] === 'Sol') {
            return 0;
        }

        $nbDays = ceil($this->panelNb / static::PANEL_BY_DAY);
        $priceByDay = static::findPriceByNbDay($prices, $nbDays);
        $basePrice = $priceByDay * $nbDays;
        return $basePrice + ($basePrice * .07) + 180;
    }

    private static function findPriceByNbDay(array $listPrices, int $nbDay): int
    {
        krsort($listPrices);

        foreach ($listPrices as $nb => $price) {
            if ($nb <= $nbDay) {
                return $price;
            }
        }
        return 100;
    }
}
