<?php

namespace App\Repository\Documentation;

use App\Entity\Documentation\DocCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DocCategory>
 *
 * @method DocCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method DocCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method DocCategory[]    findAll()
 * @method DocCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DocCategory::class);
    }
}
