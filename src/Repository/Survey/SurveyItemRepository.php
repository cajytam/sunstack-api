<?php

namespace App\Repository\Survey;

use App\Entity\Survey\SurveyItem;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SurveyItem>
 *
 * @method SurveyItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method SurveyItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method SurveyItem[]    findAll()
 * @method SurveyItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SurveyItemRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SurveyItem::class);
    }
}
