<?php

namespace App\Repository\Calculation;

use App\Entity\Calculation\Price;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Price>
 *
 * @method Price|null find($id, $lockMode = null, $lockVersion = null)
 * @method Price|null findOneBy(array $criteria, array $orderBy = null)
 * @method Price[]    findAll()
 * @method Price[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Price::class);
    }

    public function save(Price $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Price $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function deleteAllPricesWithPlaceAndType(string $place, string $typeInstallation): mixed
    {
        $qb = $this->createQueryBuilder("e")
            ->andWhere('e.installationType = :installation_type')
            ->andWhere('e.place = :place')
            ->setParameter('installation_type', $typeInstallation)
            ->setParameter('place', $place)
            ->delete();
        return $qb->getQuery()->getResult();
    }
}
