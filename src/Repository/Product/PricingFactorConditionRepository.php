<?php

namespace App\Repository\Product;

use App\Entity\Product\PricingFactorCondition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PricingFactorCondition>
 *
 * @method PricingFactorCondition|null find($id, $lockMode = null, $lockVersion = null)
 * @method PricingFactorCondition|null findOneBy(array $criteria, array $orderBy = null)
 * @method PricingFactorCondition[]    findAll()
 * @method PricingFactorCondition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PricingFactorConditionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PricingFactorCondition::class);
    }
}
