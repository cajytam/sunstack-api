<?php

namespace App\Repository\Survey;

use App\Entity\Survey\FileSurvey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FileSurvey>
 *
 * @method FileSurvey|null find($id, $lockMode = null, $lockVersion = null)
 * @method FileSurvey|null findOneBy(array $criteria, array $orderBy = null)
 * @method FileSurvey[]    findAll()
 * @method FileSurvey[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FileSurveyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FileSurvey::class);
    }
}
