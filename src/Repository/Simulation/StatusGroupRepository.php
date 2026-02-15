<?php

namespace App\Repository\Simulation;

use App\Entity\Simulation\StatusGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StatusGroup>
 *
 * @method StatusGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method StatusGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method StatusGroup[]    findAll()
 * @method StatusGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatusGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, StatusGroup::class);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getHighestSort(): int|null
    {
        $query = $this->createQueryBuilder('s');
        $query
            ->select('MAX(s.sort)')
            ->orderBy('s.sort', 'DESC');
        return $query->getQuery()->getSingleScalarResult();
    }

    public function getHigherSortStatusGroups(int $sort): array
    {
        $qb = $this->createQueryBuilder('s')
            ->where('s.sort >= :sort')
            ->setParameter('sort', $sort)
            ->orderBy('s.sort', 'ASC');
        $query = $qb->getQuery();
        return $query->execute();
    }
}
