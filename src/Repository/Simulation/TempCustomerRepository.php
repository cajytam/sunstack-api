<?php

namespace App\Repository\Simulation;

use App\Entity\Simulation\TempCustomer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TempCustomer>
 *
 * @method TempCustomer|null find($id, $lockMode = null, $lockVersion = null)
 * @method TempCustomer|null findOneBy(array $criteria, array $orderBy = null)
 * @method TempCustomer[]    findAll()
 * @method TempCustomer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TempCustomerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TempCustomer::class);
    }

    public function save(TempCustomer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(TempCustomer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
