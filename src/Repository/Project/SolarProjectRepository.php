<?php

namespace App\Repository\Project;

use App\Entity\Project\SolarProject;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SolarProject>
 *
 * @method SolarProject|null find($id, $lockMode = null, $lockVersion = null)
 * @method SolarProject|null findOneBy(array $criteria, array $orderBy = null)
 * @method SolarProject[]    findAll()
 * @method SolarProject[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SolarProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SolarProject::class);
    }
}
