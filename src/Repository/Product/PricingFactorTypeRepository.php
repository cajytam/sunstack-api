<?php

namespace App\Repository\Product;

use App\Entity\Product\PricingFactorType;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<\App\Entity\Product\PricingFactorType>
 *
 * @method \App\Entity\Product\PricingFactorType|null find($id, $lockMode = null, $lockVersion = null)
 * @method \App\Entity\Product\PricingFactorType|null findOneBy(array $criteria, array $orderBy = null)
 * @method \App\Entity\Product\PricingFactorType[]    findAll()
 * @method \App\Entity\Product\PricingFactorType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PricingFactorTypeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, \App\Entity\Product\PricingFactorType::class);
    }
}
