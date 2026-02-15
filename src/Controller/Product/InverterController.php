<?php

namespace App\Controller\Product;

use App\Factory\Calculation\Inverter;
use App\Repository\Product\PanelRepository;
use App\Utils\Temperature;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/inverters')]
class InverterController extends AbstractController
{
    #[Route('/number/{powerInstallation}/{typeInstallation}', name: 'api_inverter_number', methods: ['GET'])]
    public function invertersRequired(
        float    $powerInstallation,
        string   $typeInstallation,
        Inverter $inverterCalculator
    ): Response
    {
        return $this->json([
            'inverters' => $inverterCalculator->getRequiredInverter(
                $powerInstallation,
                $typeInstallation
            )
        ]);
    }

    #[Route('/sizing', name: 'sizing', methods: ['POST', 'GET'])]
    public function inverterSizing(
        Request         $request,
        PanelRepository $panelRepository,
        Inverter        $inverterCalculator
    ): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);

        $nbPanels = $parameters['nbPanel'];
        $idPanel = $parameters['idPanel'];
        $azimuthRatio = $parameters['azimuthRatio'];
        $chefLieu = $parameters['chefLieu'];
        $electricalPhase = $parameters['electricalPhase'];
        $inverterType = $parameters['addBatterie'] ? 'H' : ($nbPanels > 140 ? 'H' : 'R');

        $panel = $panelRepository->find($idPanel);

        $temperatureMini = Temperature::getMinimalTemperatureByDepartment(substr($chefLieu, 0, 2));

        $inverterResult = $inverterCalculator->getInverterSizingCalculation(
            $nbPanels,
            $panel,
            $azimuthRatio,
            $temperatureMini['temperature'],
            $electricalPhase,
            $inverterType,
            new \DateTimeImmutable()
        );

        $chefLieuInfos = $this->forward(
            'App\Controller\Api\ApiController::getApiGeoGouv',
            ['codeInseeVille' => $chefLieu,]
        )->getContent();

        if ($chefLieuInfos) {
            $chefLieuInfos = json_decode($chefLieuInfos, true);
        }

        $nomChefLieu = $chefLieuInfos['nom'];
        $departementChefLieu = $chefLieuInfos['departement'];
        $latChefLieu = $chefLieuInfos['centre']['coordinates'][1];
        $longChefLieu = $chefLieuInfos['centre']['coordinates'][0];

        $chefLieuResult = [
            'chefLieu' => [
                'nom' => $nomChefLieu,
                'lat' => $latChefLieu,
                'long' => $longChefLieu,
                'departement' => $departementChefLieu,
                'temperature' => $temperatureMini,
            ]];

        return $this->json(array_merge(
                $inverterResult,
                $chefLieuResult
            )
        );
    }
}
