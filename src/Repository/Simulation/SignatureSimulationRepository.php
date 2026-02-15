<?php

namespace App\Repository\Simulation;

use App\Entity\Simulation\SignatureSimulation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SignatureSimulation>
 *
 * @method SignatureSimulation|null find($id, $lockMode = null, $lockVersion = null)
 * @method SignatureSimulation|null findOneBy(array $criteria, array $orderBy = null)
 * @method SignatureSimulation[]    findAll()
 * @method SignatureSimulation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SignatureSimulationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SignatureSimulation::class);
    }
}
