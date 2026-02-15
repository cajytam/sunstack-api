<?php

namespace App\Repository\Product;

use App\Entity\Product\PricingFactorPrice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<\App\Entity\Product\PricingFactorPrice>
 *
 * @method \App\Entity\Product\PricingFactorPrice|null find($id, $lockMode = null, $lockVersion = null)
 * @method \App\Entity\Product\PricingFactorPrice|null findOneBy(array $criteria, array $orderBy = null)
 * @method \App\Entity\Product\PricingFactorPrice[]    findAll()
 * @method \App\Entity\Product\PricingFactorPrice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PricingFactorPriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, \App\Entity\Product\PricingFactorPrice::class);
    }

    public function getActivePricingFactor(\DateTimeImmutable $dateOffer = null)
    {
        if ($dateOffer === null) {
            $dateOffer = new \DateTimeImmutable();
        }

        $qb = $this->createQueryBuilder('p')
            ->where("p.startAt <= :dateOffer")
            ->setParameter('dateOffer', $dateOffer)
            ->andWhere("p.endAt >= :dateOffer OR p.endAt IS NULL")
            ->setParameter('dateOffer', $dateOffer)
            ->getQuery();
        return $qb->execute();
    }
}
