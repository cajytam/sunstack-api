<?php

namespace App\Repository\Product;

use App\Entity\Product\Panel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Panel>
 *
 * @method Panel|null find($id, $lockMode = null, $lockVersion = null)
 * @method Panel|null findOneBy(array $criteria, array $orderBy = null)
 * @method Panel[]    findAll()
 * @method Panel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PanelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Panel::class);
    }

    public function save(Panel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Panel $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getActivePanel(string $installationType, \DateTimeImmutable $currentDate = null)
    {
        if ($currentDate === null) {
            $currentDate = new \DateTimeImmutable();
        }

        $qb = $this->createQueryBuilder('p')
            ->where('p.installationType = :installationType')
            ->setParameter('installationType', $installationType)
            ->andWhere("p.debutOnSaleAt <= :currentDate")
            ->setParameter('currentDate', $currentDate)
            ->orderBy('p.debutOnSaleAt', 'DESC')
            ->getQuery();
        if (count($qb->execute()) > 0) {
            return $qb->execute()[0];
        }
        return null;
    }
}
