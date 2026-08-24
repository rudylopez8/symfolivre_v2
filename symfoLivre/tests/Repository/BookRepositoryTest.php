<?php

namespace App\Tests\Repository;

use App\Entity\Book;
use App\Entity\Category;
use App\Entity\User;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BookRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private BookRepository $bookRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->bookRepository = self::getContainer()->get(BookRepository::class);
    }

    public function testSearchByTitleFindsMatchingBook(): void
    {
        $this->createBook('Les Misérables', '978-2-07-036019-6');

        $results = $this->bookRepository->search('Misérables');

        $this->assertCount(1, $results);
        $this->assertSame('Les Misérables', $results[0]->getTitle());
    }

    public function testSearchByIsbnFindsExactMatch(): void
    {
        $this->createBook('Germinal', '978-2-07-040434-0');

        $results = $this->bookRepository->search('978-2-07-040434-0');

        $this->assertCount(1, $results);
        $this->assertSame('Germinal', $results[0]->getTitle());
    }

    public function testSearchWithNoMatchReturnsEmptyArray(): void
    {
        $this->createBook('Candide', '978-2-07-036057-8');

        $results = $this->bookRepository->search('Livre totalement inexistant xyz');

        $this->assertCount(0, $results);
    }

    private function createBook(string $title, string $isbn): Book
    {
        $author = (new User())
            ->setEmail(uniqid() . '@example.com')
            ->setPassword('not-used-in-this-test')
            ->setFirstname('Auteur')
            ->setLastname('Test')
            ->setRoles(['ROLE_AUTEUR'])
            ->setCreatedAt(new \DateTimeImmutable());

        $category = (new Category())->setLabel('Roman');

        $book = (new Book())
            ->setTitle($title)
            ->setIsbn($isbn)
            ->setType('TEXTE')
            ->setFilePath('fake.txt')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setAuthor($author)
            ->setCategory($category);

        $this->em->persist($author);
        $this->em->persist($category);
        $this->em->persist($book);
        $this->em->flush();

        return $book;
    }
}