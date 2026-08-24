<?php

namespace App\Tests\Controller\Api;

use App\Entity\Book;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookApiControllerTest extends WebTestCase
{
    public function testSearchWithoutParametersReturns400(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/books');

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testSearchByTitleReturnsMatchingBook(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $author = (new User())
            ->setEmail('api-author@example.com')
            ->setPassword('x')
            ->setFirstname('Api')
            ->setLastname('Author')
            ->setRoles(['ROLE_AUTEUR'])
            ->setCreatedAt(new \DateTimeImmutable());

        $category = (new Category())->setLabel('Sciences');

        $book = (new Book())
            ->setTitle("Introduction à l'algorithmique")
            ->setIsbn('978-2-10-000000-1')
            ->setType('TEXTE')
            ->setFilePath('fake.txt')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setAuthor($author)
            ->setCategory($category);

        $em->persist($author);
        $em->persist($category);
        $em->persist($book);
        $em->flush();

        $client->request('GET', '/api/books?title=algorithmique');

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertSame(1, $data['count']);
        $this->assertSame("Introduction à l'algorithmique", $data['results'][0]['title']);
        $this->assertSame('Api', $data['results'][0]['author']['firstname']);
    }

    public function testSearchByUnknownIsbnReturnsEmptyResults(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/books?isbn=000-0-00-000000-0');

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame(0, $data['count']);
    }
}