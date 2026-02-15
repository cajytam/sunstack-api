<?php

namespace App\Repository\Simulation;

use App\Entity\Simulation\FileTask;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FileTask>
 *
 * @method FileTask|null find($id, $lockMode = null, $lockVersion = null)
 * @method FileTask|null findOneBy(array $criteria, array $orderBy = null)
 * @method FileTask[]    findAll()
 * @method FileTask[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileTaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileTask::class);
    }
}
