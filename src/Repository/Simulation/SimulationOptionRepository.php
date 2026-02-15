<?php

namespace App\Repository\Simulation;

use App\Entity\Simulation\SimulationOption;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SimulationOption>
 */
class SimulationOptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SimulationOption::class);
    }
}
