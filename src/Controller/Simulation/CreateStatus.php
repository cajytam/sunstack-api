<?php

namespace App\Controller\Simulation;

use App\Entity\Simulation\Status;
use App\Repository\Simulation\StatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
readonly class CreateStatus
{
    public function __construct(
        private StatusRepository $statusRepository
    )
    {
    }

    /**
     * @throws Exception
     */
    public function __invoke(Status $status, EntityManagerInterface $entityManager): Status
    {
        $statutGroupId = $status->getStatusGroup()->getId();
        if (null === $status->getSort()) {
            $higherOrder = $this->statusRepository->getHighestSort($statutGroupId) ?: 0;
            $status->setSort($higherOrder + 1);
        } else {
            $higherStatuses = $this->statusRepository->getHigherSortStatusGroups($status->getSort(), $statutGroupId);
            $initialSortInt = $status->getSort() + 1;
            foreach ($higherStatuses as $higherStatus) {
                if ($higherStatus->getId() !== $status->getId()) {
                    $higherStatus->setSort($initialSortInt);
                    $entityManager->persist($higherStatus);
                    $initialSortInt++;
                }
            }
            $entityManager->flush();
        }
        return $status;
    }
}
