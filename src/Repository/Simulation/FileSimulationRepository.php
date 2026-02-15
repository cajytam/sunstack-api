<?php

namespace App\Repository\Simulation;

use App\Entity\Simulation\FileSimulation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FileSimulation>
 *
 * @method FileSimulation|null find($id, $lockMode = null, $lockVersion = null)
 * @method FileSimulation|null findOneBy(array $criteria, array $orderBy = null)
 * @method FileSimulation[]    findAll()
 * @method FileSimulation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileSimulationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileSimulation::class);
    }
}
