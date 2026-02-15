<?php

namespace App\Repository\Sale;

use App\Entity\Sale\IndependentBonus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IndependentBonus>
 *
 * @method IndependentBonus|null find($id, $lockMode = null, $lockVersion = null)
 * @method IndependentBonus|null findOneBy(array $criteria, array $orderBy = null)
 * @method IndependentBonus[]    findAll()
 * @method IndependentBonus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IndependentBonusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IndependentBonus::class);
    }

    public function save(IndependentBonus $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(IndependentBonus $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return IndependentBonus[] Returns an array of IndependentBonus objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?IndependentBonus
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
