<?php

namespace App\Repository\Simulation;

use App\Entity\Simulation\TaskCommentSimulation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TaskCommentSimulation>
 *
 * @method TaskCommentSimulation|null find($id, $lockMode = null, $lockVersion = null)
 * @method TaskCommentSimulation|null findOneBy(array $criteria, array $orderBy = null)
 * @method TaskCommentSimulation[]    findAll()
 * @method TaskCommentSimulation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskCommentSimulationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskCommentSimulation::class);
    }
}
