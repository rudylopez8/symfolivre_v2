<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    /**
     * Recherche par titre, ISBN ou catégorie.
     */
    public function search(string $query = ''): array
    {
        $qb = $this->createQueryBuilder('b')
            ->leftJoin('b.category', 'c')
            ->orderBy('b.createdAt', 'DESC');

        if ($query !== '') {
            $qb->andWhere('b.title LIKE :q OR b.isbn = :isbn OR c.label LIKE :q')
            ->setParameter('q', '%' . $query . '%')
            ->setParameter('isbn', $query);   // ← valeur brute, sans %
        }

        return $qb->getQuery()->getResult();
    }

    public function add(Book $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Book $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByIsbn(string $isbn): ?Book
    {
        return $this->findOneBy(['isbn' => $isbn]);
    }
}