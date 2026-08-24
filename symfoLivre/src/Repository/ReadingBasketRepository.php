<?php

namespace App\Repository;

use App\Entity\ReadingBasket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ReadingBasket>
 */
class ReadingBasketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ReadingBasket::class);
    }

    /**
     * Récupère le panier d'un utilisateur.
     */
    public function findByUser(\App\Entity\User $user): array
    {
        return $this->createQueryBuilder('rb')
            ->where('rb.user = :user')
            ->setParameter('user', $user)
            ->orderBy('rb.addedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function add(ReadingBasket $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ReadingBasket $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}