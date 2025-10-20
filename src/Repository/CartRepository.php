<?php

namespace App\Repository;

use App\Entity\Cart;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cart>
 */
class CartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    public function findActiveCartByUser(User $user): ?Cart
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.user = :user')
            ->andWhere('c.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'active')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findActiveCartBySession(string $sessionId): ?Cart
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.sessionId = :sessionId')
            ->andWhere('c.status = :status')
            ->setParameter('sessionId', $sessionId)
            ->setParameter('status', 'active')
            ->getQuery()
            ->getOneOrNullResult();
    }
}