<?php

namespace App\Repository\Product;

use App\Entity\Product\Inverter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Inverter>
 *
 * @method Inverter|null find($id, $lockMode = null, $lockVersion = null)
 * @method Inverter|null findOneBy(array $criteria, array $orderBy = null)
 * @method Inverter[]    findAll()
 * @method Inverter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InverterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inverter::class);
    }

    public function findAllByType(
        string      $installationType,
        string|null $electricalPhase = null
    ): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.type = :installationType')
            ->setParameter('installationType', $installationType)
            ;

        if ($electricalPhase) {
            $qb
                ->andWhere('p.electricalPhase = :electricalPhase')
                ->setParameter('electricalPhase', $electricalPhase);
        }
        $qb
            ->orderBy('p.power', 'DESC');

        $query = $qb->getQuery();

        return $query->execute();
    }

    public function findAllByTypeAndPower(string $installationType, float $power, string $electricalPhase = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.type = :installationType')
            ->andWhere('p.power >= :power')
            ->setParameter('installationType', $installationType)
            ->setParameter('power', $power);

        if ($electricalPhase) {
            $qb
                ->andWhere('p.electricalPhase = :electricalPhase')
                ->setParameter('electricalPhase', $electricalPhase);
        }

        $qb->orderBy('p.power', 'ASC');

        $query = $qb->getQuery();

        return $query->execute();
    }
}
