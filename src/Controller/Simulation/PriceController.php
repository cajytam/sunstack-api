<?php

namespace App\Controller\Simulation;

use App\Factory\Calculation\Inverter;
use App\Factory\Calculation\SimulationCalculation;
use App\Repository\Product\BatteryRepository;
use App\Repository\Product\PanelRepository;
use App\Repository\Product\PricingFactorPriceRepository;
use App\Repository\Simulation\SimulationRepository;
use App\Utils\Temperature;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/price', name: 'app_price_')]
class PriceController extends AbstractController
{
    const INSTALLATION_LOCATION = [
        'T' => 'Toiture',
        'S' => 'Sol',
        'O' => 'Ombrière'
    ];

    const TYPE_TOITURE = [
        'T' => 'Tuile',
        'A' => 'Ardoise',
        'B' => 'Bac acier',
        'F' => 'Fibrociment',
        'E' => 'EDPM',
        'O' => 'Autre',
    ];

    /**
     * @throws Exception
     */
    #[IsGranted(new Expression('is_authenticated()'))]
    #[Route('/simulation', name: 'simulation', methods: ['GET'])]
    public function getPriceSimulation(
        Request                      $request,
        Inverter                     $inverterCalculation,
        PanelRepository              $panelRepository,
        BatteryRepository            $batteryRepository,
        PricingFactorPriceRepository $factorPriceRepository,
        SimulationRepository         $simulationRepository,
        Inverter                     $inverterCalculator
    ): JsonResponse {
        $idSimulation = $request->query->get('simulation_id');
        $simulation = $idSimulation ? $simulationRepository->find($idSimulation) : null;

        $panelNb = $request->query->get('panel_nb');
        if (is_null($panelNb) && $simulation) {
            $panelNb = $simulation->getNbPanelsTotal();
        }

        $panelId = $request->query->get('panel_id');
        $panel = $simulation ? $simulation->getPanel() : $panelRepository->find($panelId);

        $chefLieu = $request->query->get('chef_lieu');
        $electricalPhase = $request->query->get('electrical_phase');
        if (is_null($electricalPhase)) {
            if ($simulation) {
                $building = $simulation->getBuildings()->first();
                if ($building) {
                    $options = $building->getSimulationOptions()->first();
                    if ($options) {
                        $electricalPhase = $options->getPhaseType();
                    }
                }
            }
        }

        $addBatteries = $request->query->get('add_batterie', true);
        $timestampOffer = $request->query->get('timestamp_offer');
        $marginSunstack = $request->query->get('margin_sunstack', 'E');
        $marginMaisonMere = $request->query->get('margin_maison_mere', 'E');
        $installationLocationId = $request->query->get('installation_location');
        if (is_null($installationLocationId) && $simulation) {
            $building = $simulation->getBuildings()->first();
            if ($building) {
                $options = $building->getSimulationOptions()->first();
                if ($options) {
                    $installationLocationId = $options->getInstallationLocation();
                }
            }
        }
        if (is_null($installationLocationId)) $installationLocationId = 'T';

        $roofTypeId = $request->query->get('roof_type');
        $batteryId = $request->query->get('battery_id');
        $batteryNb = $request->query->get('battery_nb');

        $mainSurvey = $request->query->get('main_survey');
        if (is_null($mainSurvey)) {
            $mainSurvey = $simulation && $simulation->isSurveyMainBuilding() !== NULL
                ? $simulation->isSurveyMainBuilding()
                : static::getInstallationTypeByPanelNumber($panelNb) === 'I';
        }

        $surveyOtherBuilding = $request->query->get('survey_other_building');
        if (is_null($surveyOtherBuilding)) {
            $surveyOtherBuilding = $simulation
                ? intval($simulation->getNbSurveyOtherBuildings())
                : 0;
        }

        $nbCharpentesNonVisibles = $request->query->get('charpente_non_visible');
        if (is_null($nbCharpentesNonVisibles)) {
            $nbCharpentesNonVisibles = $simulation
                ? intval($simulation->getNbCharpentesNonVisibles())
                : 0;
        }

        $nbCharpentsBeton = $request->query->get('charpente_beton');
        if (is_null($nbCharpentsBeton)) {
            $nbCharpentsBeton = $simulation
                ? intval($simulation->getNbCharpentesBeton())
                : 0;
        }

        $isAsbestos = $request->query->get('is_asbestos', false);
        $asbestos = boolval($isAsbestos);

        $buildingNb = $request->query->get('nb_building', 0);

        $customerTypeId = $request->query->get('customer_type');
        if (is_null($customerTypeId)) {
            $customerTypeId = $simulation?->getTempCustomer()->getCustomerType();
        }
        $customerType = intval($customerTypeId) === 1 ? 'Particulier' : 'Professionnel';

        $installationLocation = static::INSTALLATION_LOCATION[$installationLocationId];
        $roofType = $roofTypeId ? static::TYPE_TOITURE[$roofTypeId] : null;

        $equipments = [];

        $inverterType = $addBatteries || static::getInstallationTypeByPanelNumber($panelNb) === 'I' ? 'H' : 'R';

        if ($timestampOffer !== null) {
            $dateOffer = (new \DateTimeImmutable())->setTimestamp($timestampOffer);
        } else {
            if ($simulation) {
                $dateOffer = $simulation->getCreatedAt();
            } else {
                $dateOffer = new \DateTimeImmutable();
            }
        }

        if (is_null($chefLieu) && $simulation) {
            $chefLieu = $simulation->getInstallationDepartment();
        }

        $equipments['panel'] = [
            'brand' => $panel->getModel(),
            'power' => $panel->getPower()
        ];

        $battery = $batteryId ? $batteryRepository->find($batteryId) : null;

        $temperatureMini = Temperature::getMinimalTemperatureByDepartment(substr($chefLieu, 0, 2));
        $simulationCalculationDetails = [];

        if ($simulation) {
            $invertersByBuilding = [];

            $buildings = $simulation->getBuildings()->toArray();

            foreach ($buildings as $building) {
                $nbPanelsCurrentBuilding = 0;
                $simulationItems = $building->getSimulationItems()->toArray();
                $buildingOptions = $building->getSimulationOptions()->toArray()[0];
                $roofTypeId = $buildingOptions->getRoofType();
                $installationLocationId = $buildingOptions->getInstallationLocation();

                foreach ($simulationItems as $item) {
                    $nbPanelsCurrentBuilding += $item->getNbPanel();
                }

                $inverterCurrentBuilding = $inverterCalculator->getInverterSizingCalculation(
                    $nbPanelsCurrentBuilding,
                    $panel,
                    1,
                    $temperatureMini['temperature'],
                    $buildingOptions->getPhaseType(),
                    $buildingOptions->getAddBattery() === 'N' && $nbPanelsCurrentBuilding < 140 ? 'R' : 'H',
                    $simulation->getCreatedAt(),
                );
                $invertersByBuilding[] = $inverterCurrentBuilding;

                $simulationParams = [
                    'inverter' => $inverterCurrentBuilding['inverters']['nb'],
                    'panel' => $nbPanelsCurrentBuilding,
                    'battery' => 0, // 0 car calculé au global par simulation
                    'installation' => 0,
                    'roof_type' => static::TYPE_TOITURE[$roofTypeId],
                    'location_type' => static::INSTALLATION_LOCATION[$installationLocationId],
                    'customer_type' => $customerType,
                    'asbestos' => $buildingOptions->getAsbestosRoof() === 'A',
                    'building' => 1,
                    'wp_total' => $nbPanelsCurrentBuilding * $panel->getPower(),
                    'inverter_detail' => $inverterCurrentBuilding,
                    'is_building_erp' => $buildingOptions->isIsErpBuilding()
                ];

                $simulationCalculationDetails[] = (new SimulationCalculation(
                    $nbPanelsCurrentBuilding,
                    $panel,
                    $dateOffer,
                    $inverterCurrentBuilding['inverters']['nb'],
                    $inverterCurrentBuilding['inverters']['total_cost'],
                    $factorPriceRepository,
                    $simulationParams,
                    $marginSunstack,
                    $marginMaisonMere,
                    $batteryNb,
                    $battery,
                    null,
                    null,
                    $mainSurvey,
                    $surveyOtherBuilding,
                    $nbCharpentesNonVisibles,
                    $nbCharpentsBeton,
                    false
                ))->getPriceDetail();

                $simulationCalculationDetails[count($simulationCalculationDetails) - 1]['current_building'] = [
                    'name' => $building->getName(),
                    'pdl' => $building->getPdlNumber(),
                ];
            }

            $inverters = [
                "puissance_totale" => 0.0,
                "monophase_depassement" => 0,
                "inverters" => [
                    "nb" => 0,
                    "total_cost" => 0.0,
                    "total_power" => 0.0,
                    "nb_total_mppt" => 0,
                    "detail" => []
                ],
                "vocMax" => 0.0,
                "iscMax" => 0.0
            ];

            foreach ($invertersByBuilding as $array) {
                $inverters["puissance_totale"] += $array["puissance_totale"];
                $inverters['monophase_depassement'] += $array["puissance_totale"] ? 1 : 0;
                $inverters["inverters"]["nb"] += $array["inverters"]["nb"];
                $inverters["inverters"]["total_cost"] += $array["inverters"]["total_cost"];
                $inverters["inverters"]["total_power"] += $array["inverters"]["total_power"];
                $inverters["inverters"]["nb_total_mppt"] += $array["inverters"]["nb_total_mppt"];
                $inverters["inverters"]["detail"] = array_merge($inverters["inverters"]["detail"], $array["inverters"]["detail"]);
                $inverters["vocMax"] = max($inverters["vocMax"], $array["vocMax"]);
                $inverters["iscMax"] = max($inverters["iscMax"], $array["iscMax"]);
            }
        } else {
            $inverters = $inverterCalculation->getInverterSizingCalculation(
                $panelNb,
                $panel,
                1,
                $temperatureMini['temperature'],
                $electricalPhase,
                $inverterType,
                $dateOffer
            );

            $simulationParams = [
                'inverter' => $inverters['inverters']['nb'],
                'panel' => $panelNb,
                'battery' => $batteryNb,
                'installation' => 0,
                'roof_type' => $roofType,
                'location_type' => $installationLocation,
                'customer_type' => $customerType,
                'asbestos' => $asbestos,
                'building' => $buildingNb,
                'wp_total' => $panelNb * $panel->getPower(),
            ];

            $simulationCalculationDetails[] = new SimulationCalculation(
                $panelNb,
                $panel,
                $dateOffer,
                $inverters['inverters']['nb'],
                $inverters['inverters']['total_cost'],
                $factorPriceRepository,
                $simulationParams,
                $marginSunstack,
                $marginMaisonMere,
                $batteryNb,
                $battery,
                null,
                null,
                $mainSurvey,
                $surveyOtherBuilding,
                $nbCharpentesNonVisibles,
                $nbCharpentsBeton,
                false
            );
        }

        $simulationParams = [
            'inverter' => $inverters['inverters']['nb'],
            'panel' => $panelNb,
            'battery' => $batteryNb,
            'installation' => 1,
            'roof_type' => $roofType,
            'location_type' => $installationLocation,
            'customer_type' => $customerType,
            'asbestos' => false,
            'building' => $buildingNb,
            'wp_total' => $panelNb * $panel->getPower(),
        ];

        $simulationCalculationDetails[] = (new SimulationCalculation(
            ($simulation ? $simulation->getNbPanelsTotal() : $panelNb),
            $panel,
            $dateOffer,
            $inverters['inverters']['nb'],
            $inverters['inverters']['total_cost'],
            $factorPriceRepository,
            $simulationParams,
            $marginSunstack,
            $marginMaisonMere,
            $batteryNb,
            $battery,
            null,
            null,
            $mainSurvey,
            $surveyOtherBuilding,
            $nbCharpentesNonVisibles,
            $nbCharpentsBeton,
            true
        ))->getPriceDetail();

        if ($inverters['inverters']['nb'] <= 0) {
            $inverterCategory = Inverter::POWER_LIMIT_RESIDENTIEL < $inverters['puissance_totale'] ? 'industrielle' : 'résidentielle';
            return $this->json([
                'title' => 'Aucun onduleur trouvé',
                'error' => 'Aucun onduleur ' . ($inverterType === 'H' ? 'hybride' : 'de réseau') . ' ayant un prix au '
                    . ($simulation ? $simulation->getCreatedAt()->format('d/m/y') : $dateOffer->format('d/m/y'))
                    . " trouvé une installation " . $inverterCategory . " en " . ($electricalPhase === 'TP' ? 'triphasé' : 'monophasé')
            ]);
        }

        $invertersModel = [];

        foreach ($inverters['inverters']['detail'] as $inverterModel) {
            $invertersModel[] = [
                'brand' => $inverterModel['brand'],
                'model' => $inverterModel['model'],
                'type' => $inverterModel['type'],
                'cable_type' => $inverterModel['cable_type'],
                'cable_price' => $inverterModel['cable_price'],
                'options_price' => $inverterModel['options_price'],
                'box_erp_price' => $inverterModel['box_erp_price'],
                'box_non_erp_price' => $inverterModel['box_non_erp_price'],
            ];
        }

        $equipments['inverter'] = [
            'detail' => $invertersModel,
            'brand' => $inverters['inverters']['detail'][0]['brand'],
            'model' => $inverters['inverters']['detail'][0]['model'],
            'type' => $inverters['inverters']['detail'][0]['type'],
            'nb' => $inverters['inverters']['nb'],
            'total_power' => $inverters['puissance_totale'],
            'nb_total_mppt' => $inverters['inverters']['nb_total_mppt'],
        ];

        $simulationData = static::priceCalculationBuildingMerge($simulationCalculationDetails);
        if ($simulation) {
            $simulationData['by_building'] = $simulationCalculationDetails;
        } else {
            $simulationData['by_building'] = null;
        }

        $simulationData['equipments'] = $equipments;

        return $this->json(
            $simulationData
        );
    }

    private function getInstallationTypeByPanelNumber(int $panelNumber): string|null
    {
        if ($panelNumber >= 140) {
            return 'I';
        }
        return 'C';
    }

    private function priceCalculationBuildingMerge(array $arrays): array
    {
        $result = [];

        foreach ($arrays as $array) {
            foreach ($array as $key => $value) {
                if ($key !== 'current_building') {
                    if (is_array($value)) {
                        if (!isset($result[$key])) {
                            $result[$key] = [];
                        }
                        $result[$key] = static::priceCalculationBuildingMerge([$result[$key], $value]);
                    } else {
                        if (!isset($result[$key])) {
                            $result[$key] = 0;
                        }
                        if (!is_numeric($value)) {
                            $result[$key] = $value;
                        } else {
                            $result[$key] += $value;
                        }
                    }
                }
            }
        }

        return $result;
    }
}
