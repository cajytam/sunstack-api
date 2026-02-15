<?php

namespace App\Repository\Sale;

use App\Entity\Sale\Numbering;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Numbering>
 *
 * @method Numbering|null find($id, $lockMode = null, $lockVersion = null)
 * @method Numbering|null findOneBy(array $criteria, array $orderBy = null)
 * @method Numbering[]    findAll()
 * @method Numbering[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NumberingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Numbering::class);
    }

//    /**
//     * @return Numbering[] Returns an array of Numbering objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('n.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Numbering
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
