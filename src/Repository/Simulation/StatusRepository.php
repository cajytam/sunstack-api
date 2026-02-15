<?php

namespace App\Repository\Simulation;

use App\Entity\Simulation\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Status>
 *
 * @method Status|null find($id, $lockMode = null, $lockVersion = null)
 * @method Status|null findOneBy(array $criteria, array $orderBy = null)
 * @method Status[]    findAll()
 * @method Status[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Status::class);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getHighestSort(int $statusGroupId): int|null
    {
        $query = $this->createQueryBuilder('s');
        $query
            ->select('MAX(s.sort)')
            ->where('s.statusGroup = :status_group')
            ->setParameter('status_group', $statusGroupId)
            ->orderBy('s.sort', 'DESC');
        return $query->getQuery()->getSingleScalarResult();
    }

    public function getHigherSortStatusGroups(int $sort, int $statusGroupId): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.sort >= :sort')
            ->andWhere('s.statusGroup >= :status_group')
            ->setParameter('sort', $sort)
            ->setParameter('status_group', $statusGroupId)
            ->orderBy('s.sort', 'ASC');
        $query = $qb->getQuery();
        return $query->execute();
    }
}
