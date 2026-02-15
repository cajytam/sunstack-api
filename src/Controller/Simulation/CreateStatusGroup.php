<?php

namespace App\Controller\Simulation;

use App\Entity\Simulation\StatusGroup;
use App\Repository\Simulation\StatusGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
readonly class CreateStatusGroup
{
    public function __construct(
        private StatusGroupRepository $statusGroupRepository
    )
    {
    }

    /**
     * @throws Exception
     */
    public function __invoke(StatusGroup $statusGroup, EntityManagerInterface $entityManager): StatusGroup
    {
        if (null === $statusGroup->getSort()) {
            $higherOrder = $this->statusGroupRepository->getHighestSort() ?: 0;
            $statusGroup->setSort($higherOrder + 1);
        } else {
            $higherStatusGroups = $this->statusGroupRepository->getHigherSortStatusGroups($statusGroup->getSort());
            $initialSortInt = $statusGroup->getSort() + 1;
            foreach ($higherStatusGroups as $higherGroup) {
                if ($higherGroup->getId() !== $statusGroup->getId()) {
                    $higherGroup->setSort($initialSortInt);
                    $entityManager->persist($higherGroup);
                    $initialSortInt++;
                }
            }
            $entityManager->flush();
        }
        return $statusGroup;
    }
}
