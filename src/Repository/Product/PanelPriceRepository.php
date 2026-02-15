<?php

namespace App\Repository\Product;

use App\Entity\Product\PanelPrice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PanelPrice>
 *
 * @method PanelPrice|null find($id, $lockMode = null, $lockVersion = null)
 * @method PanelPrice|null findOneBy(array $criteria, array $orderBy = null)
 * @method PanelPrice[]    findAll()
 * @method PanelPrice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PanelPriceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PanelPrice::class);
    }
}
