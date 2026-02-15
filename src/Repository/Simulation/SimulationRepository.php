<?php

namespace App\Repository\Simulation;

use App\Entity\Simulation\Simulation;
use App\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @extends ServiceEntityRepository<Simulation>
 *
 * @method Simulation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Simulation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Simulation[]    findAll()
 * @method Simulation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SimulationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Simulation::class);
    }

    public function save(Simulation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Simulation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @throws Exception
     */
    public function getByMonth(\Datetime $date): int|null
    {
        $from = new \DateTime($date->format("Y-m-01") . " 00:00:00");
        $to = new \DateTime($date->format("Y-m-t") . " 23:59:59");

        $qb = $this->createQueryBuilder("e")
            ->select('COUNT(e.id) AS simulations');
        $qb
            ->andWhere('e.createdAt BETWEEN :from AND :to')
            ->setParameter('from', $from)
            ->setParameter('to', $to);
        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @throws Exception
     */
    public function getSignedByMonthForUser(\DateTimeImmutable $date, User $user): array
    {
        $from = new \DateTimeImmutable($date->format("Y-m-01") . " 00:00:00");
        $to = new \DateTimeImmutable($date->format("Y-m-t") . " 23:59:59");

        $qb = $this->createQueryBuilder("e")
            ->where('e.signedAt BETWEEN :from AND :to')
            ->andWhere('e.ownedBy = :user')
            ->andWhere('e.deletedAt IS NULL')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->setParameter('user', $user);

        return $qb->getQuery()->execute();
    }

    /*public function findUsersCreateSimulation(\DateTime $dateDebut = null, \DateTime $dateFin = null): array
    {
        $from = $dateDebut ? new \DateTime($dateDebut->format("Y-m-t") . " 00:00:00") : null;
        $to = $dateFin ? new \DateTime($dateFin->format("Y-m-t") . " 23:59:59") : null;

        $entityManager = $this->getEntityManager();

        $query = $entityManager->createQuery(
            'SELECT u, s
            FROM App\Entity\Simulation\Simulation s
            INNER JOIN s.ownedBy u
            WHERE s.deletedAt IS NULL
            GROUP BY s.ownedBy'
        );

        return $query->getResult();
    }*/
}
