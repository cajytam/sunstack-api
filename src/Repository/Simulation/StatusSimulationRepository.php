<?php

namespace App\Repository\Simulation;

use App\Entity\Simulation\StatusSimulation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StatusSimulation>
 *
 * @method StatusSimulation|null find($id, $lockMode = null, $lockVersion = null)
 * @method StatusSimulation|null findOneBy(array $criteria, array $orderBy = null)
 * @method StatusSimulation[]    findAll()
 * @method StatusSimulation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatusSimulationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatusSimulation::class);
    }
}
