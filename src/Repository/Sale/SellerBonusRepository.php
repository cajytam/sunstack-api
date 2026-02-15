<?php

namespace App\Repository\Sale;

use App\Entity\Sale\SellerBonus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SellerBonus>
 *
 * @method SellerBonus|null find($id, $lockMode = null, $lockVersion = null)
 * @method SellerBonus|null findOneBy(array $criteria, array $orderBy = null)
 * @method SellerBonus[]    findAll()
 * @method SellerBonus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SellerBonusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SellerBonus::class);
    }

//    /**
//     * @return SellerBonus[] Returns an array of SellerBonus objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SellerBonus
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
