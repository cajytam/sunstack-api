<?php

namespace App\Repository\Documentation;

use App\Entity\Documentation\DocHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DocHistory>
 *
 * @method DocHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method DocHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method DocHistory[]    findAll()
 * @method DocHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocHistoryRepository extends ServiceEntityRepository
{
    const UPLOAD_CODE = 4;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocHistory::class);
    }

    public function findLastUpdateDate(int $docFileId): \DateTimeImmutable|null
    {
        $lastUpdatedDate = $this->createQueryBuilder('d')
            ->select('d.doneAt')
            ->andWhere('d.document = :document')
            ->setParameter('document', $docFileId)
            ->andWhere('d.action = ' . static::UPLOAD_CODE)
            ->orderBy('d.doneAt', 'DESC')
            ->getQuery()
            ->getResult();
        if (count($lastUpdatedDate) > 0) {
            return $lastUpdatedDate[0]['doneAt'];
        }
        return null;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function findIsUserEverInteract(int $userId, int $docFileId): bool
    {
        $q = $this
            ->createQueryBuilder('d')
            ->select('COUNT(d.id)')
            ->andWhere('d.user = :user')
            ->setParameter('user', $userId)
            ->andWhere('d.document = :document')
            ->setParameter('document', $docFileId);
        $lastUpdateDate = $this->findLastUpdateDate($docFileId);
        if ($lastUpdateDate) {
            $q
                ->andWhere('d.doneAt >= :lastUpdateDate')
                ->setParameter('lastUpdateDate', $lastUpdateDate);
        }
        return ($q->getQuery()->getSingleScalarResult() > 0);
    }

    public function save(DocHistory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DocHistory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
