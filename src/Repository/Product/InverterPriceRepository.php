<?php

namespace App\Repository\Product;

use App\Entity\Product\InverterPrice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InverterPrice>
 *
 * @method InverterPrice|null find($id, $lockMode = null, $lockVersion = null)
 * @method InverterPrice|null findOneBy(array $criteria, array $orderBy = null)
 * @method InverterPrice[]    findAll()
 * @method InverterPrice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InverterPriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InverterPrice::class);
    }
}
