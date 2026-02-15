<?php

namespace App\Controller\Simulation;

use App\Factory\Calculation\Calculator;
use App\Repository\Calculation\PriceRepository;
use App\Repository\Product\PanelRepository;
use App\Repository\Simulation\ProfileRepository;
use App\Repository\Simulation\SimulationRepository;
use App\Repository\Simulation\ZoneRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'app_')]
class CalculationController extends AbstractController
{
    const float DEFAULT_REDUCED_YIELD_OTHER_YEARS_RATE = .55;
    const int DEFAULT_ESCALATION_PRICE_RATE = 6;
    const float DEFAULT_ESCALATION_SALES_PRICE_RATE = 1.5;

    #[Route('/calculation', name: 'calculation', methods: ['GET'])]
    public function SimulationCalculation(
        Request              $request,
        Calculator           $calculator,
        ZoneRepository       $zoneRepository,
        ProfileRepository    $profileRepository,
        PriceRepository      $priceRepository,
        PanelRepository      $panelRepository,
        SimulationRepository $simulationRepository
    ): JsonResponse
    {
        $installations = $request->query->all()['installations'];
        $idProfile = $request->query->get('profile');
        $nbPanels = $request->query->get('nb_panels');
        $installationPrice = $request->query->get('installation_price', 0);
        $energyConsumption = $request->get('energy_consumption', 0);
        $energyPrice = $request->query->get('energy_price');
        $zoneDepartment = $request->query->get('department');
        $zonePanelTilt = $request->query->get('panel_tilt');
        $zoneRoofOrientation = $request->query->get('roof_orientation');
        $vatRate = $request->query->get('vat');
        $rateReducedYield = $request->query->get('reduced_yield', self::DEFAULT_REDUCED_YIELD_OTHER_YEARS_RATE);
        $rateReducedYieldFirstYear = $request->query->get('reduced_yield_first_year');
        if ('' === $rateReducedYieldFirstYear) $rateReducedYieldFirstYear = null;
        $rateEscalationPrice = $request->query->get('price_escalation', self::DEFAULT_ESCALATION_PRICE_RATE);
        $rateEscalationSalesPrice = $request->query->get('sales_price_escalation', self::DEFAULT_ESCALATION_SALES_PRICE_RATE);
        $idPanel = $request->query->get('panel');
        $installationLocation = $request->query->get('installation_location');
        $simulationId = $request->query->get('simulation_id');

        if (null === $nbPanels) {
            if (null !== $installations) {
                $nbPanels = 0;
                foreach ($installations as $v) {
                    $nbPanels += intval($v['nbPanel']);
                }
            }
        }

        if (!$energyConsumption) $energyConsumption = 0;
        if (!$energyPrice) $energyPrice = 0;

        if (null === $installationPrice) {
            $price = $priceRepository->findOneBy(['nbPanels' => \intval($nbPanels)]);
            if (null === $price) {
                return new JsonResponse(
                    [
                        'error' => "Le montage de $nbPanels panneaux n'est pas disponible"
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            } else {
                $installationPrice = $price->getPriceBasic();
            }
        }

        if (
            null !== $zoneDepartment &&
            null !== $zonePanelTilt &&
            null !== $zoneRoofOrientation
        ) {
            $zone = $zoneRepository->findOneBy([
                'department' => $zoneDepartment,
                'panelTilt' => $zonePanelTilt,
                'roofOrientation' => $zoneRoofOrientation,
            ]);

            if (null === $zone) {
                return new JsonResponse(
                    [
                        'message' => "Pas de zone définie pour le département [$zoneDepartment], inclinaison de [$zonePanelTilt], et orientation [$zoneRoofOrientation]"
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }
        } else {
            $zone = null;
        }

        $profile = $profileRepository->find($idProfile);
        if (null === $profile) {
            return new JsonResponse(
                [
                    "error" => "Profile avec l'ID $idProfile non existant"
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $panel = $panelRepository->find($idPanel);
        if (null === $panel) {
            return new JsonResponse(
                [
                    "error" => "Panel avec l'ID $idPanel non existant"
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $dateRaccordement = $simulationId ? $simulationRepository->find($simulationId)->getCreatedAt() : null;

        if ($simulationId == 304) {
            $limitedConsumption = 0.5;
        } elseif ($simulationId == 400) {
            $limitedConsumption = 2721.18;
        } else {
            $limitedConsumption = 1;
        }

        $yearReport = $calculator->generateSimulationTable(
            $profile,
            $panel,
            $zone,
            $nbPanels,
            $installationPrice,
            $energyConsumption,
            $energyPrice,
            $vatRate,
            $rateReducedYield,
            $rateReducedYieldFirstYear,
            $rateEscalationPrice,
            $rateEscalationSalesPrice,
            $installations,
            $installationLocation,
            $simulationId,
            $limitedConsumption,
            $dateRaccordement
        );

        return new JsonResponse($yearReport);
    }
}
