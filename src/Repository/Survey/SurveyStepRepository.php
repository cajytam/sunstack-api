<?php

namespace App\Repository\Survey;

use App\Entity\Survey\SurveyStep;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SurveyStep>
 *
 * @method SurveyStep|null find($id, $lockMode = null, $lockVersion = null)
 * @method SurveyStep|null findOneBy(array $criteria, array $orderBy = null)
 * @method SurveyStep[]    findAll()
 * @method SurveyStep[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SurveyStepRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurveyStep::class);
    }
}
