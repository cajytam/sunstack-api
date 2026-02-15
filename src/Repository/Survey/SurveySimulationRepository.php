<?php

namespace App\Repository\Survey;

use App\Entity\Survey\SurveySimulation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SurveySimulation>
 *
 * @method SurveySimulation|null find($id, $lockMode = null, $lockVersion = null)
 * @method SurveySimulation|null findOneBy(array $criteria, array $orderBy = null)
 * @method SurveySimulation[]    findAll()
 * @method SurveySimulation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SurveySimulationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurveySimulation::class);
    }
}
