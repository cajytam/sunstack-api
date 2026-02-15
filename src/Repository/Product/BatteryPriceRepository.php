<?php

namespace App\Repository\Product;

use App\Entity\Product\BatteryPrice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BatteryPrice>
 *
 * @method BatteryPrice|null find($id, $lockMode = null, $lockVersion = null)
 * @method BatteryPrice|null findOneBy(array $criteria, array $orderBy = null)
 * @method BatteryPrice[]    findAll()
 * @method BatteryPrice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BatteryPriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BatteryPrice::class);
    }

    //    /**
    //     * @return BatteryPrice[] Returns an array of BatteryPrice objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?BatteryPrice
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
