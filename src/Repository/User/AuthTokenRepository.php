<?php

namespace App\Repository\User;

use App\Entity\User\AuthToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AuthToken>
 *
 * @method AuthToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuthToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuthToken[]    findAll()
 * @method AuthToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AuthToken::class);
    }
}
