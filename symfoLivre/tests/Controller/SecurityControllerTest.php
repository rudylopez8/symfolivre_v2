<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
    }

    public function testLoginWithValidCredentialsRedirects(): void
    {
        $client = static::createClient();
        $this->createUser($client, 'login-ok@example.com', 'password123');

        $client->request('GET', '/login');
        $client->submitForm('Se connecter', [
            '_username' => 'login-ok@example.com',
            '_password' => 'password123',
        ]);

        $this->assertResponseRedirects();
    }

    public function testLoginWithInvalidCredentialsShowsError(): void
    {
        $client = static::createClient();

        $client->request('GET', '/login');
        $client->submitForm('Se connecter', [
            '_username' => 'unknown@example.com',
            '_password' => 'wrong-password',
        ]);
        $client->followRedirect();

        $this->assertSelectorExists('.alert-danger');
    }

    private function createUser(\Symfony\Bundle\FrameworkBundle\KernelBrowser $client, string $email, string $plainPassword): User
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $hasher = static::getContainer()->get(UserPasswordHasherInterface::class);

        $user = (new User())
            ->setEmail($email)
            ->setFirstname('Test')
            ->setLastname('User')
            ->setRoles([])
            ->setCreatedAt(new \DateTimeImmutable());
        $user->setPassword($hasher->hashPassword($user, $plainPassword));

        $em->persist($user);
        $em->flush();

        return $user;
    }
}