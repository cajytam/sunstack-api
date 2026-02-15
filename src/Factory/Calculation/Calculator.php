<?php

namespace App\Factory\Calculation;

use App\Entity\Calculation\Fees;
use App\Entity\Calculation\Index;
use App\Entity\Calculation\IndexPrice;
use App\Entity\Calculation\Site;
use App\Entity\Calculation\YearReport;
use App\Entity\Enum\FeesType;
use App\Entity\Product\Panel;
use App\Entity\Simulation\Profile;
use App\Entity\Simulation\Zone;

// NOTE : le champ energyPotential est nommé index dans cette class
class Calculator
{
    const int DECIMAL_NUMBER = 2;

    public function getSite(
        Profile            $profile,
        Panel              $panel,
        int                $nbPanels,
        float              $installationPrice,
        float              $energyConsumption,
        float              $energyPrice,
        Zone|null          $zone,
        float              $rateEscalationPrice,
        array|null         $installations,
        string|null        $installationLocation,
        float              $limitedConsumption = 1,
        \DateTimeImmutable $dateRaccordement = new \DateTimeImmutable()
    ): Site
    {

        $site = new Site();
        $site->setProfile($profile);
        $site->setInstallationPrice($installationPrice);
        $site->setEnergyConsumption($energyConsumption);
        $site->setEnergyPrice($energyPrice);

        // Panel
        $site->setPanel($panel);

        // Zone
        if (null !== $zone) {
            $site->setZoneIndex($zone->getEnergyPotential());
        } else {
            $tempNbPanel = 0;
            $temp = 0;
            foreach ($installations as $v) {
                $temp += ($v['index'] * $v['nbPanel']);
                $tempNbPanel += intval($v['nbPanel']);
            }
            $site->setZoneIndex($temp / $tempNbPanel);
        }

        // Escalation of prices rate
        $escalationPrice = 1 + ($rateEscalationPrice / 100);

        // Indexes
        $indexes = [];
        foreach (range(1, 1) as $year) {
            $index = new Index();
            $index->setRate(1);
            $index->setYear($year);
            $indexes[] = $index;
        }
        foreach (range(2, 25) as $year) {
            $index = new Index();
            $index->setRate($escalationPrice);
            $index->setYear($year);
            $indexes[] = $index;
        }

        $indexesPrices = [];
        $currentPrice = $site->getEnergyConsumption() * $site->getEnergyPrice();
        foreach ($indexes as $index) {
            if ($index->getYear() > 1) {
                $currentPrice = $currentPrice * (1 + $index->getRate());
            }
            $indexPrice = new IndexPrice();
            $indexPrice->setYear($index->getYear());
            $indexPrice->setRate($index->getRate());
            $indexPrice->setAmount($currentPrice);
            $indexesPrices[] = $indexPrice;
        }

        $site->setIndexes($indexesPrices);

        // Fees
        $fees = new Fees();
        $fees->setType(FeesType::INVEST);
        $fees->setYear(1);
        $fees->setAmount(-$site->getInstallationPrice());
        $site->setFees([$fees]);

        $totalPowerKva = ($site->getPanel()->getPower() * $nbPanels) / 1000;

        if ($site->getProfile()->isIsEligibleForBonus() && 'T' === $installationLocation) {
            $tauxPremiereAnnee = $totalPowerKva > 9 ? .8 : 1;

            $bonusFees = [];
            $totalAmountBonus = static::getBonus($totalPowerKva, null, $dateRaccordement);
            $prime = new Fees();
            $fees->setType(FeesType::PRIMES);
            $prime->setYear(2);
            $prime->setAmount($totalAmountBonus * $tauxPremiereAnnee);
            array_push($bonusFees, $fees, $prime);

            if ($tauxPremiereAnnee !== 1) {
                foreach (range(3, 6) as $year) {
                    $prime = new Fees();
                    $fees->setType(FeesType::PRIMES);
                    $prime->setYear($year);
                    $prime->setAmount($totalAmountBonus * .05);
                    $bonusFees[] = $prime;
                }
            }
            $site->setFees($bonusFees);
        }
        return $site;
    }

    private static function getBonusPrices(\DateTimeImmutable $dateRaccordement): null|array
    {
        // Ordre du plus grand au plus petit, avec en clé le nombre de panneau "à partir de"
        $bonusPrices = [
            '2023-01-01' => [
                3 => 510,
                9 => 380,
                36 => 210,
                100 => 110,
                '>100' => 0
            ],
            '2024-01-04' => [
                3 => 370,
                9 => 280,
                36 => 200,
                100 => 100,
                '>100' => 0
            ],
            '2024-03-16' => [
                3 => 350,
                9 => 260,
                36 => 200,
                100 => 100,
                '>100' => 0
            ],
            '2024-04-24' => [
                3 => 300,
                9 => 230,
                36 => 200,
                100 => 100,
                '>100' => 0
            ],
        ];

        return static::findValueAfterDate($bonusPrices, $dateRaccordement);
    }

    public static function getBonus(
        float $totalKva, bool|null $isEligibleToBonus = null, \DateTimeImmutable $dateRaccordement = new \DateTimeImmutable()
    ): null|float
    {
        if (null !== $isEligibleToBonus) {
            if (!$isEligibleToBonus) {
                return null;
            }
        }

        $bonusPrices = static::getBonusPrices($dateRaccordement);

        return match (true) {
            $totalKva <= 3 => $totalKva * $bonusPrices['3'],
            $totalKva <= 9 => $totalKva * $bonusPrices['9'],
            $totalKva <= 36 => $totalKva * $bonusPrices['36'],
            $totalKva <= 100 => $totalKva * $bonusPrices['100'],
            $totalKva > 100 => $bonusPrices['>100'],
            default => null,
        };
    }

    public function generateSimulationTable(
        Profile            $profile,
        Panel              $panel,
        Zone|null          $zone,
        int                $nbPanels,
        float|null         $installationPrice,
        float|null         $energyConsumption,
        float|null         $energyPrice,
        int                $vatRate,
        float              $rateReducedYield,
        float|null         $rateReducedYieldFirstYear,
        float              $rateEscalationPrice,
        float              $rateEscalationSalesPrice,
        array|null         $installations,
        string|null        $installationLocation,
        int                $idSimulation,
        float              $limitedConsumption = 1,
        \DateTimeImmutable $dateRaccordement = new \DateTimeImmutable(),
    ): array
    {
        // price with VAT
        if ($vatRate > 0) {
            $installationPrice *= (1 + $vatRate / 100);
        }

        if ($energyConsumption === null) $energyConsumption = 0;
        if ($energyPrice === null) $energyPrice = 0;

        $site = self::getSite(
            $profile,
            $panel,
            $nbPanels,
            $installationPrice,
            $energyConsumption,
            $energyPrice,
            $zone,
            $rateEscalationPrice,
            $installations,
            $installationLocation,
            $limitedConsumption,
            $dateRaccordement
        );
        $powerInstallation = $site->getPanel()->getPower() * $nbPanels;

        // Create list (previous & next)
        $reports = [];
        foreach (range(1, 25) as $year) {
            $yearReport = new YearReport();
            $yearReport->setYear($year);

            $reports[] = $yearReport;
        }

        foreach ($reports as $currentReport) {
            foreach ($reports as $previousReport) {
                if (null === $currentReport->getPrevious() && $currentReport->getYear() - 1 === $previousReport->getYear()) {
                    $currentReport->setPrevious($previousReport);
                }
            }
            foreach ($reports as $nextReport) {
                if (null === $currentReport->getNext() && $currentReport->getYear() + 1 === $nextReport->getYear()) {
                    $currentReport->setNext($nextReport);
                }
            }
        }

        // Populate list
        foreach ($reports as $yearReport) {
            $index = $site->getIndex($yearReport->getYear());

            // Production
            $productionPerPanel = $site->getPanel()->getPower();
            $totalProduction = $productionPerPanel * $nbPanels;

            /*
                impact de la zone sur la production (inclinaison + orientation + département)
            */
            $totalProduction *= $site->getZoneIndex() / 1000;

            /*
                impact de l'usure sur les panneaux
            */
            if (1 < $yearReport->getYear()) {
                $totalRateReduced = $rateReducedYieldFirstYear !== null
                    ? ((($rateReducedYield / 100) * ($yearReport->getPrevious()->getYear())) + ($rateReducedYieldFirstYear / 100))
                    : ($rateReducedYield / 100) * $yearReport->getPrevious()->getYear();

                $totalProduction *= 1 - $totalRateReduced;
            } else if ($rateReducedYieldFirstYear !== null) {
                $totalProduction *= 1 - ($rateReducedYieldFirstYear / 100);
            }
            $yearReport->setProduction(round($totalProduction, 2));

            $rateConsumption = $site->getProfile()->getConsumptionRate();

            // consommation
            $consumption = $yearReport->getProduction() * $rateConsumption;
            if ($consumption > $energyConsumption || $limitedConsumption > 1) {
                if ($limitedConsumption > 1) {
                    $consumption = $limitedConsumption;
                } else {
                    $consumption = $energyConsumption * $limitedConsumption;
                }
            }

            $yearReport->setConsumption($consumption);
            $yearReport->setInjection(intval($rateConsumption) === 1 ? 0 : $yearReport->getProduction() - $yearReport->getConsumption());

            // Invoice
            if ($yearReport->getPrevious()) {
                $yearReport->setIndexedKwhCost($yearReport->getPrevious()->getIndexedKwhCost() * $index->getRate());
            } else {
                $yearReport->setIndexedKwhCost($site->getEnergyPrice());
            }
            $yearReport->setInvoice($yearReport->getIndexedKwhCost() * $energyConsumption);
            $yearReport->setNewInvoice(($energyConsumption - $yearReport->getConsumption()) * $yearReport->getIndexedKwhCost());

            // Investment
            $investment = 0;
            foreach ($site->getFees() as $fee) {
                if ($fee->getYear() === $yearReport->getYear() && $fee->getAmount() < 0) {
                    $investment += $fee->getAmount();
                }
            }
            $yearReport->setInvestment($investment);

            $injectionAmount = $site->getProfile()->getInjectionPrice(
                $yearReport->getYear(),
                $powerInstallation / 1000,
                $yearReport->getInjection(),
                $dateRaccordement,
                $installationLocation
            );

            // TODO temporaire en attendant un feat pour forcer prix de revente par devis
            // Ne concerne que le devis -	RVT-2404-0029 - CHATIN- BERTRAND (ID 701) + ARP-2407-0035 - REGIONAL EXPRESS (ID 1013)
            if ($idSimulation === 701) {
                $injectionAmount = .07 * $yearReport->getInjection();
            } elseif($idSimulation === 1013) {
                $injectionAmount = .065 * $yearReport->getInjection();
            }

            // Sales
            if ($yearReport->getPrevious()) {
                $yearReport->setSale(
                    pow(1 + ($rateEscalationSalesPrice / 100), $yearReport->getYear() - 1)
                    * $injectionAmount
                );
            } else {
                $yearReport->setSale($injectionAmount);
            }

            // Prime
            $prime = 0;
            foreach ($site->getFees() as $fee) {
                if ($fee->getYear() === $yearReport->getYear() && $fee->getAmount() > 0) {
                    $prime += $fee->getAmount();
                }
            }
            $yearReport->setPrime($prime);

            // Profit
            $yearReport->setProfit($yearReport->getInvoice() - $yearReport->getNewInvoice() + $yearReport->getSale() + $yearReport->getPrime());
        }

        // Calculate diff
        foreach ($reports as $yearReport) {
            match (null === $yearReport->getPrevious()) {
                true => $yearReport->setDiff(
                    $yearReport->getProfit() - abs($yearReport->getInvestment())
                ),
                false => $yearReport->setDiff(
                    $yearReport->getPrevious()->getDiff() + $yearReport->getProfit()
                ),
            };
        }

        return array_map(function (YearReport $yearReport) {
            return [
                'year' => round($yearReport->getYear(), self::DECIMAL_NUMBER),
                'production' => round($yearReport->getProduction(), 2),
                'consumption' => round($yearReport->getConsumption(), self::DECIMAL_NUMBER),
                'injection' => round($yearReport->getInjection(), self::DECIMAL_NUMBER),
                'indexed_kwh_cost' => round($yearReport->getIndexedKwhCost(), 4),
                'invoice' => round($yearReport->getInvoice(), self::DECIMAL_NUMBER),
                'sale' => round($yearReport->getSale(), self::DECIMAL_NUMBER),
                'new_invoice' => round($yearReport->getNewInvoice(), self::DECIMAL_NUMBER),
                'investment' => round($yearReport->getInvestment(), self::DECIMAL_NUMBER),
                'prime' => round($yearReport->getPrime(), self::DECIMAL_NUMBER),
                'profit' => round($yearReport->getProfit(), self::DECIMAL_NUMBER),
                'diff' => round($yearReport->getDiff(), self::DECIMAL_NUMBER),
                'pivot' => $yearReport->getPivot()
            ];
        }, $reports);
    }

    private static function findValueAfterDate($listPDCPrice, \DateTimeImmutable $dateRaccordement)
    {
        krsort($listPDCPrice);

        foreach ($listPDCPrice as $date => $values) {
            if ($date <= $dateRaccordement->format('Y-m-d')) {
                return $values;
            }
        }
        return null;
    }
}
