<?php

namespace App\Repository\Product;

use App\Entity\Product\Battery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Battery>
 *
 * @method Battery|null find($id, $lockMode = null, $lockVersion = null)
 * @method Battery|null findOneBy(array $criteria, array $orderBy = null)
 * @method Battery[]    findAll()
 * @method Battery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BatteryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Battery::class);
    }

    //    /**
    //     * @return Battery[] Returns an array of Battery objects
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

    //    public function findOneBySomeField($value): ?Battery
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
