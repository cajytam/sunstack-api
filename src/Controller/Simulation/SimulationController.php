<?php

namespace App\Controller\Simulation;

use App\Repository\Simulation\SimulationRepository;
use App\Utils\Generator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/simulations', name: 'app_api_simulation_')]
class SimulationController extends AbstractController
{
    const int SIMULATION_IDENTIFIER_LENGTH = 15;

    #[Route('/identifier/generate', name: 'identifier', methods: ['GET'])]
    public function generateIdentifier(
        SimulationRepository $simulationRepository
    ): JsonResponse
    {
        $isUnique = false;
        do {
            $identifier = Generator::generateRandomString(static::SIMULATION_IDENTIFIER_LENGTH);
            if (null === $simulationRepository->findOneBy([
                    'identifier' => $identifier
                ])) {
                $isUnique = true;
            }
        } while (!$isUnique);

        return $this->json([
            'identifier' => $identifier,
        ]);
    }
}
