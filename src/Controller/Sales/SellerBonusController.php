<?php

namespace App\Controller\Sales;

use App\Entity\Simulation\Simulation;
use App\Entity\User\User;
use App\Repository\Simulation\SimulationRepository;
use App\Repository\User\UserRepository;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'app_')]
class SellerBonusController extends AbstractController
{
    const BONUS_PER_WP = [
        '75%' => [
            'price' => 0.0035,
            'target' => 240000
        ],
        '100%' => [
            'price' => 0.008,
            'target' => 320000
        ],
        '125%' => [
            'price' => 0.01,
            'target' => 400000
        ],
        '150%' => [
            'price' => 0.015,
            'target' => 480000
        ],
    ];

    public function __construct(
        private readonly SimulationRepository $simulationRepository,
    )
    {
    }

    /**
     * @throws Exception
     */
    #[Route('/sales/bonus', name: 'sales_bonus', methods: ['GET'])]
    public function getBonusByTotalWc(
        Request        $request,
        UserRepository $userRepository
    ): JsonResponse
    {
        $userId = $request->query->get('user_id');
        $powerTotal = $request->query->get('total_power', 0);
        $timestampOffer = $request->query->get('timestamp_offer');

        $user = null;
        if ($userId) {
            $user = $userRepository->find($userId);
        }

        if ($timestampOffer !== null) {
            $dateOffer = (new \DateTimeImmutable())->setTimestamp($timestampOffer);
        } else {
            $dateOffer = new \DateTimeImmutable();
        }

        $userWcSigned = 0;
        $monthSimulations = static::getWattCrete($dateOffer, $user);

        /** @var Simulation $simulation */
        foreach ($monthSimulations as $simulation) {
            $userWcSigned += $simulation->getPanel()->getPower() * $simulation->getNbPanelsTotal();
        }

        return $this->json([
            'steps' => static::getPointStep($userWcSigned, $powerTotal),
            'userWcSigned' => $userWcSigned,
            'earnPoints' => static::getPointStep($userWcSigned, $userWcSigned, true),
        ]);
    }

    /**
     * @throws Exception
     */
    private function getWattCrete(\DateTimeImmutable $date, User $user): array
    {
        return $this->simulationRepository->getSignedByMonthForUser(
            $date,
            $user
        );
    }

    private function getPointStep(float|int $totalWcSigned, float|int $totalWc, bool $isTotalBonus = false): array
    {
        $result = [];
        $isCurrentStep = false;

        if ($isTotalBonus) {
            $totalWc = $totalWcSigned;
        }

        foreach (static::BONUS_PER_WP as $key => $value) {
            if (!$isCurrentStep) {
                $percent = static::calculatePercentage($totalWcSigned, $value['target']);

                if ($percent < 1) {
                    $isCurrentStep = true;
                }
            } else {
                $percent = 0;
            }

            $currentStepFlag = $key === array_key_last(static::BONUS_PER_WP) && $percent === 1
                || ($isCurrentStep && $percent < 1 && $percent > 0)
                || $isCurrentStep && $key === array_key_first(static::BONUS_PER_WP);

            $result[] = [
                'currentStep' => intval($currentStepFlag),
                'price' => $value['price'],
                'target' => $value['target'],
                'name' => $key,
                'percent' => round($percent * 100, 2),
                'potentialBonus' => round($totalWc * $value['price'], 2),
                'untilNextStep' => $currentStepFlag ? $value['target'] - $totalWcSigned : 0
            ];
        }

        return $result;
    }

    private function calculatePercentage(float|int $totalWc, int $target): float|int
    {
        $ratio = $totalWc / $target;
        if ($ratio >= 1) {
            return 1;
        }
        return round($ratio, 2);
    }
}
