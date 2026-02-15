<?php

namespace App\Controller\PDF;

use App\Entity\Customer\Building;
use App\Entity\Customer\PDL;
use App\Entity\Simulation\Simulation;
use App\Factory\Calculation\Calculator;
use App\Factory\Calculation\Inverter;
use App\Factory\File\FileFactory;
use App\Factory\File\PDFFactory;
use App\Repository\Calculation\PriceRepository;
use App\Utils\Temperature;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class SimulationPDFController extends AbstractController
{
    const COLOR_SUNSTACK = '#36B691';
    const SEUIL_UNITE_PRODUCTION_PREMIERE_ANNEE = 1000000;

    #[Route('/pdf/generate/{identifier}/{action}', name: 'app_create_2024_pdf', methods: ['GET'])]
    #[IsGranted('PDF_SIMULATION', 'simulation')]
    public function generatePDFSimulation(
        Simulation  $simulation,
        Calculator  $calculator,
        Inverter    $inverterCalculator,
        string      $identifier,
        string|null $action = null
    ): Response {
        $pdfFilename = '';
        $generateDir = $this->getParameter('kernel.cache_dir') . '/simulation/pdf/';
        $fullName = $generateDir . $simulation->getIdentifier() . '.pdf';

        FileFactory::createDir($generateDir);

        $installations = [];
        foreach ($simulation->getSimulationItems()->toArray() as $item) {
            $installations[] = [
                'nbPanel' => $item->getNbPanel(),
                'index' => $item->getEnergyPotential()
            ];
        };

        $customer = $customerStreet = $customerCity = $customerPostcode = null;

        // Customer address
        if ($simulation->getTempCustomer() !== null) {
            $customer = $simulation->getTempCustomer();
            $customerStreet = trim($customer->getStreetNumber() . ' ' . $customer->getStreetName() . ' ' . $customer->getStreetPostBox());
            $customerCity = $customer->getStreetCity();
            $customerPostcode = $customer->getStreetPostcode();
        }

        $dateRaccordement = $simulation->getCreatedAt() ?: null;

        $profile = $simulation->getProfile();

        $consumptionPrice = $simulation->getConsumptionPrice();
        $consumptionQuantity = $simulation->getConsumptionQuantity();

        $reducedYield = $simulation->getReducedYield();
        $reducedYieldFirstYear = $simulation->getReducedYieldFirstYear();

        $priceEscalation = $simulation->getPriceEscalation();
        $salesPriceEscalation = $simulation->getSalesPriceEscalation() ?: 0;

        $customerType = (null !== $customer->getCompanyName() ? 2 : 1);

        $canDeductVAT = $customer->isCanDeductVAT();

        $panel = $simulation->getPanel();

        $nbPanels = 0;

        if (null !== $installations) {
            foreach ($installations as $v) {
                $nbPanels += intval($v['nbPanel']);
            }
        }

        $powerInstallationKWc = $nbPanels * $panel->getPower() / 1000;

        $temperatureMini = Temperature::getMinimalTemperatureByDepartment(substr($customerPostcode, 0, 2));

        $buildings = $simulation->getBuildings()->toArray();

        $inverters = [];
        $pdlNumbers = [];
        $installationLocation = null;
        foreach ($buildings as $building) {
            $nbPanelsCurrentBuilding = 0;
            $pdlNumbers[] = $building->getPdlNumber();
            $simulationItems = $building->getSimulationItems()->toArray();
            foreach ($simulationItems as $item) {
                $nbPanelsCurrentBuilding += $item->getNbPanel();
            }
            $buildingOptions = $building->getSimulationOptions()->toArray()[0];
            $installationLocation = $buildingOptions->getInstallationLocation();

            $inverters[] = $inverterCalculator->getInverterSizingCalculation(
                $nbPanelsCurrentBuilding,
                $panel,
                1,
                $temperatureMini['temperature'],
                $buildingOptions->getPhaseType(),
                $buildingOptions->getAddBattery() === 'N' && $nbPanelsCurrentBuilding < 140 ? 'R' : 'H',
                $simulation->getCreatedAt(),
            );
        }

        $pdlNumbers = array_unique($pdlNumbers);

        $totalPowerInverter = 0;
        $nbInverters = 0;
        $nbInvertersMPPT = 0;

        $inverterBrand = [];
        $inverterType = [];

        foreach ($inverters as $inverter) {
            $totalPowerInverter += array_key_exists('total_power', $inverter['inverters']) ? $inverter['inverters']['total_power'] : 0;
            $nbInverters += array_key_exists('nb', $inverter['inverters']) ? $inverter['inverters']['nb'] : 0;
            if (array_key_exists('detail', $inverter['inverters'])) {
                foreach ($inverter['inverters']['detail'] as $inverterDetail) {
                    $inverterBrand[] = $inverterDetail['brand'];
                    $inverterType[] = $inverterDetail['type'];
                    $nbInvertersMPPT += $inverterDetail['mppt']['nb'] ?: 0;
                }
            }
        }

        $inverterBrand = array_unique($inverterBrand);
        $inverterType = array_values(array_unique($inverterType));

        if ($simulation->getId() === 304) {
            $totalPowerInverter = 170;
            $nbInverters = 2;
        }

        // adresse installation
        if ($simulation->isIsSameAddresses()) {
            $pdlStreet = $customerStreet;
            $pdlCity = $customerCity;
            $pdlPostcode = $customerPostcode;
        } else {
            $pdlStreet = trim($simulation->getInstallationStreetNumber() . ' ' . $simulation->getInstallationStreetName());
            $pdlCity = $simulation->getInstallationStreetCity();
            $pdlPostcode = $simulation->getInstallationStreetPostcode();
        }
        $ownerShipTxt = static::getOwnershipCustomer($buildings[0]);

        $vatRate = $powerInstallationKWc <= 3 && floatval($profile->getConsumptionRate()) !== floatval(0) ? 10 : 20;

        if ($simulation->getManualPrice()) {
            $installationPrice = $simulation->getManualPrice();
        } elseif ($simulation->getFinalPriceHT()) {
            $installationPrice = $simulation->getFinalPriceHT();
        } elseif ($simulation->getInstallationPriceHT()) {
            $installationPrice = $simulation->getInstallationPriceHT();
        } else {
            $installationPrice = 0;
        }

        if ($simulation->getId() === 304) {
            $limitedConsumption = 0.5;
        } elseif ($simulation->getId() == 400) {
            $limitedConsumption = 2721.18;
        } else {
            $limitedConsumption = 1;
        }

        $yearReport = $calculator->generateSimulationTable(
            $profile,
            $panel,
            null,
            $nbPanels,
            $installationPrice,
            $consumptionQuantity,
            $consumptionPrice,
            ($canDeductVAT ? 0 : $vatRate),
            $reducedYield,
            $reducedYieldFirstYear,
            $priceEscalation,
            $salesPriceEscalation,
            $installations,
            $installationLocation,
            $simulation->getId(),
            $limitedConsumption,
            $dateRaccordement
        );

        $consumptions = [];
        $consumptionPrice25Years = 0;
        $productionTotal = 0;
        $earning25Years = 0;
        $totalCost = 0;
        $firstYearProduction = 0;
        $totalBonus = 0;
        $monthlyEconomiesTotal = 0;
        $monthlyEdfSalesTotal = 0;
        $earningFirstYear = 0;
        $lastYearMonthlyCalculate = 10;
        //        $lastYearMonthlyCalculate = $customerType === 2 ? 10 : 7;

        foreach ($yearReport as $report) {
            if (intval($report['year']) <= $lastYearMonthlyCalculate) {
                $monthlyEconomiesTotal += $report['invoice'] - $report['new_invoice'];
                $monthlyEdfSalesTotal += $report['sale'];
            }

            if (intval($report['year']) === 1) {
                $earningFirstYear = $report['profit'];
                $firstYearProduction = $report['production'];
            }

            $consumptions[] = [
                'year' => intval($report['year']),
                'amount' => $report['indexed_kwh_cost'] * $report['consumption']
            ];
            $productionTotal += $report['production'];
            $consumptionPrice25Years += $report['invoice'];
            $earning25Years += $report['profit'];
            $totalCost += $report['investment'];

            $totalBonus += $report['prime'];
        }
        $rateLoanInvestment = ($customerType === 2 ? 4 : 6) / 100;

        $monthlyLoanPayment = static::calculateMonthlyPayment(
            $totalCost,
            $rateLoanInvestment,
            $lastYearMonthlyCalculate * 12
        );

        $monthlyEconomy = $monthlyEconomiesTotal / (12 * $lastYearMonthlyCalculate);
        $monthlyEdfSales = $monthlyEdfSalesTotal / (12 * $lastYearMonthlyCalculate);

        $realInvestment = ($monthlyEconomy + $monthlyEdfSales) - $monthlyLoanPayment;

        if ($firstYearProduction > static::SEUIL_UNITE_PRODUCTION_PREMIERE_ANNEE) {
            $firstYearProduction /= static::SEUIL_UNITE_PRODUCTION_PREMIERE_ANNEE;
            $unitFirstYearProduction = 'MWh';
            $isWithoutDecimalsFirstYearProduction = false;
        } else {
            $unitFirstYearProduction = 'Wh';
            $isWithoutDecimalsFirstYearProduction = true;
        }

        $bilanCarbone = $productionTotal * 480;
        if ($bilanCarbone > 1000000) {
            $bilanCarboneSentence = rtrim(rtrim(number_format($bilanCarbone / 1000000, 2, ",", " "), '0'), ',') . ' T';
        } else {
            $bilanCarboneSentence = rtrim(rtrim(number_format($bilanCarbone / 1000, 2, ",", " "), '0'), ',') . ' T';
        }

        $totalPVGIS = [];
        $inclinaisons = [];

        foreach ($buildings as $building) {
            foreach ($building->getSimulationItems() as $item) {
                $total = 0;
                $inclinaisons[] = $item->getInclinaison();
                foreach ($item->getDetailedEnergyPotential() as $k => $v) {
                    array_key_exists($k, $totalPVGIS) ?: $totalPVGIS[$k] = 0;
                    $totalPVGIS[$k] += $v;
                    $total += $v;
                }
                array_key_exists('total', $totalPVGIS) ?: $totalPVGIS['total'] = 0;
                $totalPVGIS['total'] += $total;
            }
        }

        $inclinaisons = array_unique($inclinaisons);

        $baseFirstYear = $yearReport[0];
        $tableConsumption = [];
        foreach ($totalPVGIS as $k => $v) {
            if ($k !== 'total') {
                $tableConsumption[$k] = $baseFirstYear['injection'] > 0 ? round(($baseFirstYear['injection'] * $v) / $totalPVGIS['total'], 2) : 0;
            }
        }

        $customerReprentative = $customer->getRepresentativeFirstname()
            ? $customer?->getRepresentativeCivility() . ' ' . $customer?->getRepresentativeFirstname() . ' ' . $customer->getRepresentativeLastname()
            : $customer->getCivility() . ' ' . $customer->getFirstname() . ' ' . $customer->getLastname();

        if ($installationLocation === 'S') {
            $structureName = 'SIGUESOL';
        } else {
            if ($simulation->getRoofType() === 'T') {
                $structureName = 'ESDEC';
            } elseif ($simulation->getRoofType() === 'B') {
                $structureName = 'K2';
            } elseif ($simulation->getRoofType() === 'O') {
                $structureName = 'Mecosun';
            } else {
                $structureName = 'AVASCO';
            }
        }

        $data = [
            'number' => $simulation->getName(),
            'date' => date_format($simulation->getCreatedAt(), 'd/m/Y'),
            'customer' => [
                'vat_rate' => $vatRate,
                'name' => $customer?->getFullName(),
                'email' => $customer?->getEmail(),
                'type' => $customer->getCustomerType(),
                'company_name' => $customer?->getCompanyName(),
                'company_siret' => $customer?->getSiret(),
                'can_deduct_vat' => $canDeductVAT,
                'title' => true,
                'phone' => $customer?->getPhoneNumber(),
                'representative' => [
                    'fullname' => $customerReprentative,
                    'position' => $customer->getRepresentativePosition()
                ]
            ],
            'address' => [
                'street' => $pdlStreet,
                'city' => $pdlCity,
                'postcode' => $pdlPostcode,
            ],
            'owner' => [
                'name' => trim($simulation->getOwnedBy()?->getFirstname() . ' ' . $simulation->getOwnedBy()?->getLastname()),
                'email' => trim($simulation->getOwnedBy()?->getEmail()),
                'title' => trim($simulation->getOwnedBy()?->getTitle()),
                'location' => trim($simulation->getOwnedBy()?->getLocation()),
                'phone' => static::formatTel(trim($simulation->getOwnedBy()?->getPhone())),
            ],
            'consumption' => [
                'price_25_year' => $consumptionPrice25Years
            ],
            'solar_panel' => [
                'nb' => $nbPanels,
                'model' => strtok($panel->getModel(), ' '),
                'power' => $panel->getPower() . ' ' . 'Wc',
                'total_power' => ($panel->getPower() * $nbPanels) / 1000
            ],
            'solar_dc' => [
                'nb' => $nbInverters,
                'total_power' => $totalPowerInverter,
                'brand' => count($inverterBrand) > 1 ? 'Plusieurs' : (count($inverterBrand) === 1 ? $inverterBrand[0] : 'A définir'),
                'type' =>
                count($inverterType) > 1
                    ? static::getInverterTypeName($inverterType[0]) . ' et ' . static::getInverterTypeName($inverterType[1])
                    : (
                        count($inverterType) === 1
                        ? static::getInverterTypeName($inverterType[0])
                        : 'A définir'
                    ),
                'nb_mppt' => $nbInvertersMPPT
            ],
            'installation' => [
                'earn' => $earning25Years - abs($totalCost),
                'first_year_production' => $firstYearProduction,
                'bilan_carbone' => $bilanCarboneSentence,
                'bonus' => $totalBonus,
                'style_pose' => static::getInstallationLocationName($installationLocation),
                'inclinaison' => count($inclinaisons) > 1 ? 'Plusieurs' : (count($inclinaisons) > 0 ? $inclinaisons[0] . '°' : '-'),
                'stability_survey_needed' => !$simulation->isSurveyMainBuilding() || $simulation->isSurveyMainBuilding() === false ? 'Non' : 'Oui',
                'structure_name' => $structureName
            ],
            'charge_transfer' => [
                'monthly_economy' => $monthlyEconomy,
                'monthly_oa_sales' => $monthlyEdfSales,
                'monthly_loan_payment' => $monthlyLoanPayment,
                'real_investment' => $realInvestment * -1,
                'rate' => $rateLoanInvestment * 100,
            ],
            'amortisationTable' => $yearReport,
            'price_escalation' => $priceEscalation,
            'sales_price_escalation' => $salesPriceEscalation,
            'works_duration' => ceil($nbPanels / 35) . " jour" . ($nbPanels > 35 ? "s" : "")
        ];

        /**
         * FIN Récupération données
         */

        if (isset($data['number'])) {
            $pdfFilename = 'SunStack - Devis ' . str_replace('/', '-', $data['number']) . '.pdf';
        }

        $sourceDevis = $this->getParameter('assets_dir') . 'pdf/simulation.pdf';

        $pdf = new PDFFactory(
            $sourceDevis,
            [
                'font-family' => 'poppins',
                'font-size' => 12,
            ],
            [4, 7]
        );

        $companyName = '';
        $companyNameBis = '';
        if (strlen(trim($data['customer']['company_name'])) > 35) {
            $tmpSplit = explode(' ', trim($data['customer']['company_name']));
            foreach ($tmpSplit as $str) {
                if ((strlen($companyName) + strlen($str)) < 35) {
                    $companyName .= ' ' . $str;
                } else {
                    $companyNameBis .= ' ' . $str;
                }
            }
        } else {
            $companyName = trim($data['customer']['company_name']);
        }

        $signatureBase64 = null;
        $signatureDateFull = null;
        $signatureName = '';
        $isAccordClauseSuspensive = false;
        $signatures = $simulation->getSignatureSimulations();

        foreach ($signatures as $sign) {
            if ($sign->getPurpose() === 'devis') {
                $signature = $sign->getSignature();

                if ($signature->getLastnameSignataire()) {
                    $signatureName .= $signature->getLastnameSignataire() . ' ';
                }
                if ($signature->getFirstnameSignataire()) {
                    $signatureName .= $signature->getFirstnameSignataire();
                }
                if ($signature->getTypeSignature()) {
                    $signatureName .= ' (' . $signature->getTypeSignature() . ')';
                }

                $signatureBase64 = $signature->getContent();
                $isAccordClauseSuspensive = $signature->isIsClauseSuspensive();
                if ($signature->getUpdatedAt()) {
                    $signatureDateFull = 'Le ' . $signature->getUpdatedAt()->format('d/m/Y \à H\hi');
                } else {
                    $signatureDateFull = 'Le ' . $signature->getCreatedAt()->format('d/m/Y \à H\hi');
                }
            }
        }

        $pdf
            ->page(1)
            ->addText(
                texte: $data['number'],
                posX: 79,
                posY: 17,
                params: [
                    'color' => self::COLOR_SUNSTACK,
                    'font-size' => 16,
                    'font-weight' => 'B'
                ],
            )
            ->addText(
                texte: $data['date'],
                posX: 27.5,
                posY: 25,
            )
            ->addText(
                texte: strtoupper($data['customer']['name']),
                posX: 46.5,
                posY: 30.5,
            )
            ->addText(
                texte: $data['owner']['name'],
                posX: 12.75,
                posY: 272.5,
                params: ['font-weight' => 'B']
            )
            ->addText(
                texte: '' === $data['owner']['title'] ? '' : ' - ' . $data['owner']['title'],
                posX: (14.75 + static::countWitdh($data['owner']['name'])),
                posY: 272.5,
                params: ['font-weight' => 'I']
            )
            ->addText(
                texte: 'email : ' . $data['owner']['email'] . ('' === $data['owner']['phone'] ? '' : '  -  mobile : ' . $data['owner']['phone']),
                posX: 12.75,
                posY: 277,
                params: [
                    'font-size' => 10
                ]
            )
            ->page(2)
            ->addText(
                texte: $data['consumption']['price_25_year'],
                posX: $data['consumption']['price_25_year'] > 1000000 ? 171.5 : 172.5,
                posY: 107,
                params: [
                    'font-weight' => 'B',
                    'font-size' => $data['consumption']['price_25_year'] > 1000000 ? 10 : 11,
                ],
                formatNumber: true
            )
            ->addText(
                texte: $data['charge_transfer']['monthly_economy'],
                posX: 41,
                posY: 229,
                params: [
                    'font-weight' => 'B',
                    'color' => '#FFFFFF'
                ],
                formatNumber: true
            )
            ->addText(
                texte: $data['charge_transfer']['monthly_oa_sales'],
                posX: 98,
                params: [
                    'font-weight' => 'B',
                    'color' => '#FFFFFF'
                ],
                formatNumber: true
            )
            ->addText(
                texte: $data['charge_transfer']['monthly_loan_payment'],
                posX: 151,
                params: [
                    'font-weight' => 'B',
                    'color' => '#FFFFFF'
                ],
                formatNumber: true
            )
            ->addText(
                texte: $data['charge_transfer']['real_investment'],
                posX: 54,
                posY: 250,
                params: [
                    'font-weight' => 'B',
                    'color' => self::COLOR_SUNSTACK
                ],
                formatNumber: true
            )
            ->addText(
                texte: $data['charge_transfer']['rate'],
                posX: 163.5,
                posY: 265,
                params: [
                    'font-size' => 8,
                    'font-weight' => 'B',
                ],
            )
            ->page(3);

        $fontWeight = 'R';
        $fontColor = '#000000';
        $initialPosY = 86.5;
        foreach ($tableConsumption as $row) {
            $initialPosX = 82;
            $pdf->addText(
                texte: $baseFirstYear['consumption'] / 12,
                posX: $initialPosX,
                posY: $initialPosY,
                params: [
                    'font-size' => 11,
                    'font-weight' => $fontWeight,
                    'color' => $fontColor
                ],
                formatNumber: true,
            );
            $pdf->addText(
                texte: intval($profile->getConsumptionRate()) === 1 ? $baseFirstYear['consumption'] / 12 : $row,
                posX: $initialPosX + 34,
                posY: $initialPosY,
                params: [
                    'font-size' => 11,
                    'font-weight' => $fontWeight,
                    'color' => $fontColor
                ],
                formatNumber: true,
            );
            $pdf->addText(
                texte: intval($profile->getConsumptionRate()) === 1 ? $baseFirstYear['consumption'] / 12 : min($baseFirstYear['consumption'] / 12, $row * $profile->getConsumptionRate()),
                posX: $initialPosX + 71,
                posY: $initialPosY,
                params: [
                    'font-size' => 11,
                    'font-weight' => $fontWeight,
                    'color' => $fontColor
                ],
                formatNumber: true,
            );
            $initialPosY += 5.25;
        }

        $pdf->page(4);
        // Si client = pro
        if (2 === $customerType) {
            $pdf
                ->addText(
                    texte: 'Raison sociale : ',
                    posX: 33.5,
                    posY: strlen($companyNameBis) > 0 ? 46.5 : 48.5,
                    params: [
                        'font-size' => 12,
                        'font-weight' => 'B'
                    ]
                )
                ->addText(
                    texte: strtoupper($companyName),
                    posX: 68,
                    params: [
                        'font-size' => 12,
                    ]
                )
                ->addText(
                    texte: strtoupper($companyNameBis),
                    posY: 51,
                    params: [
                        'font-size' => 12,
                    ]
                )
                ->addText(
                    texte: 'N° de SIRET : ',
                    posX: 33.5,
                    posY: 54.5,
                    params: [
                        'font-size' => 12,
                        'font-weight' => 'B'
                    ]
                )
                ->addText(
                    texte: null === $data['customer']['company_siret'] ? '' : $data['customer']['company_siret'],
                    posX: 60,
                    params: [
                        'font-size' => 12,
                    ]
                );
        }
        $pdf
            ->addText(
                texte: $data['customer']['representative']['fullname'],
                posX: 65,
                posY: 66,
                params: [
                    'font-size' => 12,
                ]
            )
            ->addText(
                texte: $data['customer']['phone'],
                posX: 48,
                posY: 77.5,
                params: [
                    'font-size' => 12,
                ]
            )
            ->addText(
                texte: $data['customer']['email'],
                posX: 115,
                params: [
                    'font-size' => 12,
                ]
            )
            ->addText(
                texte: $data['address']['street'],
                posX: 72,
                posY: 89,
                params: [
                    'font-size' => 12,
                ]
            )
            ->addText(
                texte: $data['address']['postcode'],
                posX: 108,
                posY: 100.75,
                params: [
                    'font-size' => 12,
                ]
            )
            ->addText(
                texte: $data['address']['city'],
                posX: 135,
                params: [
                    'font-size' => 12,
                ]
            );

        foreach ($pdlNumbers as $k => $v) {
            $pdf->addText(
                texte: $v,
                posX: 32,
                posY: 100.75 + ($k * 5),
                params: [
                    'font-size' => 12,
                ]
            );
        }

        $pdf->addText(
            texte: $data['solar_panel']['model'] . ' ' . $data['solar_panel']['power'],
            posX: 70.5,
            posY: 151,
            params: [
                'font-size' => 12,
            ]
        )
            ->addText(
                texte: $data['solar_panel']['nb'],
                posY: 156.75,
                params: [
                    'font-size' => 12,
                ]
            )
            ->addText(
                texte: rtrim(rtrim(number_format($data['solar_panel']['total_power'], 5, ",", " "), '0'), ',') . ' kWc',
                posY: 162,
                params: [
                    'font-size' => 12,
                ],
                formatNumber: true
            )
            ->addText(
                texte: (!$isWithoutDecimalsFirstYearProduction
                    ? rtrim(rtrim(number_format($data['installation']['first_year_production'], 3, ",", " "), '0'), ',')
                    : number_format($data['installation']['first_year_production'], 0, ",", " ")) . ' ' . $unitFirstYearProduction,
                posY: 167.25,
                params: [
                    'font-size' => 12,
                ]
            )
            ->addText(
                texte: $data['installation']['style_pose'],
                posY: 178,
                params: [
                    'font-size' => 12,
                ]
            )
            ->addText(
                texte: $data['installation']['inclinaison'],
                posY: 183.25,
                params: [
                    'font-size' => 12,
                ]
            )
            ->addText(
                texte: $data['installation']['structure_name'],
                posY: 188.5,
                params: [
                    'font-size' => 12,
                ]
            )
            ->addText(
                texte: $data['solar_dc']['brand'],
                posX: 150,
                posY: 151,
                params: [
                    'font-size' => 12,
                ]
            )
            ->addText(
                texte: $data['solar_dc']['type'],
                posY: 156.75,
                params: [
                    'font-size' => 12,
                ]
            )
            ->addText(
                texte: $data['solar_dc']['nb'],
                posY: 162,
                params: [
                    'font-size' => 12,
                ]
            )
            ->addText(
                texte: rtrim(rtrim(number_format($data['solar_dc']['total_power'] / 1000, 3, ",", " "), '0'), ',') . ' kVA',
                posY: 167.25,
                params: [
                    'font-size' => 12,
                ]
            )
            ->addText(
                texte: $data['solar_dc']['nb_mppt'],
                posY: 172.5,
                params: [
                    'font-size' => 12,
                ]
            )
            ->addText(
                texte: $data['solar_dc']['nb_mppt'],
                posY: 172.5,
                params: [
                    'font-size' => 12,
                ]
            )
            ->addText(
                texte: $data['installation']['stability_survey_needed'],
                posX: 84,
                posY: 268,
                params: [
                    'font-size' => 12,
                    'font-weight' => 'B',
                ]
            )
            ->addText(
                texte: $data['works_duration'],
                posX: 170,
                params: [
                    'font-size' => 12,
                    'font-weight' => 'B',
                ]
            );
        // BATTERIE et BORNE A FAIRE

        $pdf->page(5)
            ->addText(
                texte: $data['solar_panel']['power'],
                posX: 81.5,
                posY: 54,
                params: [
                    'font-size' => 12,
                    'font-weight' => 'B',
                ]
            )
            ->addText(
                texte: $data['solar_panel']['nb'],
                posX: 168,
                posY: 53.5,
                params: [
                    'font-size' => 12,
                    'font-weight' => 'B',
                ]
            )
            ->addText(
                texte: rtrim(rtrim(number_format($data['solar_dc']['total_power'] / 1000, 3, ",", " "), '0'), ','),
                posX: 168,
                posY: 58.75,
                params: [
                    'font-size' => 12,
                    'font-weight' => 'B',
                ]
            )
            ->addText(
                texte: rtrim(rtrim(number_format($data['solar_panel']['nb'] * .5, 2, ",", " "), '0'), ','),
                posY: 86,
                params: [
                    'font-size' => 12,
                    'font-weight' => 'B',
                ]
            )
            ->addText(
                texte: $installationPrice,
                posX: 156,
                posY: 178,
                params: [
                    'font-size' => 10,
                    'font-weight' => 'B'
                ],
                formatNumber: true
            )
            ->addText(
                texte: $installationPrice,
                posX: 57,
                posY: 187.75,
                params: [
                    'font-size' => 10,
                    'font-weight' => 'B'
                ],
                formatNumber: true
            )
            ->addText(
                texte: $data['customer']['vat_rate'],
                posX: 110,
                params: [
                    'font-size' => 10,
                    'font-weight' => 'B'
                ],
                formatNumber: false
            )
            ->addText(
                texte: (1 + $data['customer']['vat_rate'] / 100) * $installationPrice,
                posX: 157,
                posY: 188,
                params: [
                    'font-size' => 12,
                    'font-weight' => 'B'
                ],
                formatNumber: true
            )
            ->addText(
                texte: (0 === intval($data['installation']['bonus']) ? 'Non éligible' : $data['installation']['bonus']),
                posX: (0 === intval($data['installation']['bonus']) ? 142 : 144.5),
                posY: 199,
                params: [
                    'font-size' => (0 === intval($data['installation']['bonus']) ? 11 : 12),
                    'font-weight' => 'B'
                ],
                formatCurrency: 0 !== intval($data['installation']['bonus']),
            )
            ->addText(
                texte: ((1 + $data['customer']['vat_rate'] / 100) * $installationPrice * 0.3),
                posX: 163,
                posY: 206,
                formatCurrency: true
            )
            ->addText(
                texte: $isAccordClauseSuspensive ? 'X' : '',
                posX: 28.5,
                posY: 213.5,
                params: [
                    'font-size' => 15,
                    'font-weight' => 'B'
                ]
            )
            ->addText(
                texte: $signatureDateFull,
                posX: 40,
                posY: 223.5,
                params: [
                    'font-size' => 12,
                ],
            )
            ->addText(
                texte: '30',
                posX: 46,
                posY: 232.25,
                params: [
                    'font-size' => 9,
                ],
            )
            ->addText(
                texte: '70',
                posX: 64,
                params: [
                    'font-size' => 9,
                ],
            )
            ->addText(
                texte: $ownerShipTxt ?: '',
                posX: 27,
                posY: 242,
                params: [
                    'font-size' => 10,
                    'font-weight' => 'I'
                ]
            );
        if ($signatureBase64) {
            $pdf
                ->addImageBase64(
                    contentBase64: $signatureBase64,
                    width: 48,
                    height: 48,
                    posX: 126,
                    posY: 224
                );
        }

        $pdf->addVerticalText(
            $simulation->getCreatedAt()->add(\DateInterval::createFromDateString('1 month'))->format('d/m/Y'),
            48,
            100,
            191,
            154,
            [
                'font-size' => 16,
                'font-weight' => 'B'
            ]
        );

        $pdf->page(6)
            ->addText(
                texte: $data['customer']['can_deduct_vat'] ? 'HT' : 'TTC',
                posX: 65,
                posY: 26,
                params: [
                    'font-size' => 12,
                    'font-weight' => 'B'
                ],
            )
            ->addText(
                texte: $data['price_escalation'],
                posX: 123.75,
                params: [
                    'font-size' => 12,
                    'font-weight' => 'B'
                ],
            )
            ->addText(
                texte: $data['sales_price_escalation'],
                posX: 193,
                params: [
                    'font-size' => 12,
                    'font-weight' => 'B'
                ],
            );

        $indexesWithCurrencyFormat = ['invoice', 'sale', 'new_invoice', 'investment', 'prime', 'profit', 'diff'];
        $indexesWithNumberFormatWithDecimals = ['production', 'consumption', 'injection'];
        $listX = [17, 17, 17, 17, 17, 20, 17, 18, 19, 18, 16, 17];
        $fontWeight = 'R';
        $fontColor = '#000000';
        $initialPosY = 46.5;
        foreach ($data['amortisationTable'] as $row) {
            $initialPosX = 15;
            $index = 0;
            foreach ($row as $rowKey => $rowValue) {
                if ('pivot' === $rowKey || 'year' === $rowKey) {
                    $fontWeight = match ($row['pivot']) {
                        -1 => 'I',
                        0 => 'B',
                        1 => 'R'
                    };
                    $fontColor = match ($row['pivot']) {
                        -1 => '#6A6A6A',
                        0, 1 => '#000000'
                    };
                } else {
                    $pdf->addText(
                        texte: '0.0' !== $rowValue ? $rowValue : '',
                        posX: $initialPosX,
                        posY: $initialPosY,
                        params: [
                            'font-size' => 7,
                            'font-weight' => $fontWeight,
                            'color' => $fontColor
                        ],
                        formatCurrency: in_array($rowKey, $indexesWithCurrencyFormat),
                        formatNumberWithoutDecimal: in_array($rowKey, $indexesWithNumberFormatWithDecimals),
                    );
                    $initialPosX += $listX[$index];
                }
                $index++;
            }
            $initialPosY += 9.75;
        }

        $pdf
            ->page(7)
            ->addText(
                texte: $data['charge_transfer']['monthly_economy'],
                posX: 26.75,
                posY: 85.25,
                params: [
                    'font-size' => 20,
                    'font-weight' => 'B',
                    'color' => static::COLOR_SUNSTACK
                ],
                formatNumber: true
            )
            ->addText(
                texte: $data['charge_transfer']['monthly_oa_sales'],
                posX: 25.5,
                posY: 139,
                params: [
                    'font-size' => 20,
                    'font-weight' => 'B',
                    'color' => static::COLOR_SUNSTACK
                ],
                formatNumber: true
            )
            ->addText(
                texte: $data['charge_transfer']['monthly_loan_payment'],
                posX: 26.5,
                posY: 195,
                params: [
                    'font-size' => 20,
                    'font-weight' => 'B',
                    'color' => static::COLOR_SUNSTACK
                ],
                formatNumber: true
            )
            ->addText(
                texte: $data['charge_transfer']['real_investment'],
                posX: 26,
                posY: 252,
                params: [
                    'font-size' => 20,
                    'font-weight' => 'B',
                ],
                formatNumber: true
            );

        $pdf->savePDFOnServer(
            $generateDir,
            $identifier . '.pdf'
        );

        if (
            $action !== 'onlyDevis' && $simulation->getCreatedAt() > (new \DateTime('2023-10-10')) && count($simulation->getSignatureSimulations()) > 0
            || "withMandat" === $action
        ) {
            $mandatRepresentation = $this->forward('App\Controller\PDF\AdministrativePDFController::generatePDFRepresentationMandate', [
                'simulation' => $simulation,
                'action' => 'onlyGenerate',
                'signature' => 'devis',
            ]);

            if ($mandatRepresentation->getContent()) {
                $fileMandat = json_decode($mandatRepresentation->getContent())->file;

                $files = [
                    $generateDir . $identifier . '.pdf',
                    $fileMandat,
                ];

                $pdfsMerged = new PDFFactory();
                $pdfsMerged->mergePDF($files);

                $pdfsMerged->savePDFOnServer(
                    $generateDir,
                    $identifier . '.pdf'
                );
            }
        }

        if ("onlyGenerate" === $action) {
            return $this->json([
                'file' => $generateDir . $identifier . '.pdf'
            ]);
        }
        $response = new BinaryFileResponse($fullName);
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_INLINE,
            $pdfFilename
        );
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    private function formatTel(string $str): string
    {
        $res = '';
        for ($i = strlen($str); $i >= 0; $i--) {
            if ($i % 2 == 0) {
                $res = substr($str, $i, 2) . ' ' . $res;
            }
        }
        return $res;
    }

    private function countWitdh(string $text): float
    {
        $lowWidth = ['l', 'i', 't', 'j'];
        $highWidth = ['m'];

        $value = 0;

        for ($i = 0; $i < strlen($text); $i++) {
            if (in_array($text[$i], $lowWidth, true)) {
                $value += 1.2;
            } elseif (in_array($text[$i], $highWidth, true)) {
                $value += 3.5;
            } else {
                $value += 2.6;
            }
        }
        return $value;
    }

    private function getOwnershipCustomer(PDL|Building $obj): string|null
    {
        if ($obj->isIsCustomerCertifiesOwnership()) {
            return "J'atteste être légalement propriétaire de l'Immeuble.";
        } elseif ($obj->isIsAgreementBareOwner()) {
            return "J'atteste être locataire de l'Immeuble et bénéficier d'une autorisation écrite du propriétaire.";
        } else {
            return null;
        }
    }

    static function calculateMonthlyPayment($amount, $annualInterestRate, $numberOfMonths): float|int
    {
        if ($annualInterestRate > 1) $annualInterestRate /= 100;
        $numerateur = (abs($amount) * ($annualInterestRate / 12));
        $denominateur = (1 - pow((1 + $annualInterestRate / 12), $numberOfMonths * -1));
        return $numerateur / $denominateur;
    }

    static function getInstallationLocationName(string $type): string
    {
        $TYPE = [
            'T' => 'Toit',
            'S' => 'Sol',
            'O' => 'Ombrière'
        ];
        return $TYPE[$type];
    }

    static function getInverterTypeName(string $type): string
    {
        $TYPE = [
            'R' => 'Réseau',
            'H' => 'Hybride',
        ];
        return $TYPE[$type];
    }
}
