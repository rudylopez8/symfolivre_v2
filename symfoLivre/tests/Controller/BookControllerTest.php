<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class BookControllerTest extends WebTestCase
{
    public function testBookIndexIsPubliclyAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/book');

        $this->assertResponseIsSuccessful();
    }

    public function testAnonymousIsRedirectedToLoginOnNewBookForm(): void
    {
        $client = static::createClient();
        $client->request('GET', '/book/new');

        $this->assertResponseRedirects('/login');
    }

    public function testLecteurCannotAccessNewBookForm(): void
    {
        $client = static::createClient();
        $lecteur = $this->createUser($client, ['ROLE_USER']);

        $client->loginUser($lecteur);
        $client->request('GET', '/book/new');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAuteurCanAccessNewBookForm(): void
    {
        $client = static::createClient();
        $auteur = $this->createUser($client, ['ROLE_AUTEUR']);

        $client->loginUser($auteur);
        $client->request('GET', '/book/new');

        $this->assertResponseIsSuccessful();
    }

    private function createUser(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client, array $roles): User
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = (new User())
            ->setEmail(uniqid() . '@example.com')
            ->setFirstname('Test')
            ->setLastname('User')
            ->setRoles($roles)
            ->setCreatedAt(new \DateTimeImmutable());
        $user->setPassword($hasher->hashPassword($user, 'password123'));

        $em->persist($user);
        $em->flush();

        return $user;
    }
}