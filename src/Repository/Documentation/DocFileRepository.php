<?php

namespace App\Repository\Documentation;

use App\Entity\Documentation\DocFile;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DocFile>
 *
 * @method DocFile|null find($id, $lockMode = null, $lockVersion = null)
 * @method DocFile|null findOneBy(array $criteria, array $orderBy = null)
 * @method DocFile[]    findAll()
 * @method DocFile[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocFileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocFile::class);
    }
}
