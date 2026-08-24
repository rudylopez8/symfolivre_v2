<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Book;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends WebTestCase
{
    public function testNonAdminCannotAccessUserList(): void
    {
        $client = static::createClient();
        $auteur = $this->createUser($client, ['ROLE_AUTEUR']);

        $client->loginUser($auteur);
        $client->request('GET', '/admin/users');

        $this->assertResponseStatusCodeSame(403);
    }

    public function testAdminCanAccessUserList(): void
    {
        $client = static::createClient();
        $admin = $this->createUser($client, ['ROLE_ADMIN']);

        $client->loginUser($admin);
        $client->request('GET', '/admin/users');

        $this->assertResponseIsSuccessful();
    }

    public function testAdminCannotDeleteThemselves(): void
    {
        $client = static::createClient();
        $admin = $this->createUser($client, ['ROLE_ADMIN']);
        $client->loginUser($admin);

        // On récupère le vrai formulaire de suppression rendu dans la page,
        // avec son jeton CSRF déjà généré — pas de manipulation manuelle du token.
        $crawler = $client->request('GET', '/admin/users');
        $form = $crawler->filter('form[action="/admin/users/' . $admin->getId() . '"]')->form();

        $client->submit($form);

        $this->assertResponseRedirects('/admin/users');
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-danger', 'propre compte');
    }

    public function testCannotDeleteAuthorWithBooks(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $admin = $this->createUser($client, ['ROLE_ADMIN']);
        $auteur = $this->createUser($client, ['ROLE_AUTEUR']);

        $category = (new Category())->setLabel('Test-' . uniqid());
        $book = (new Book())
            ->setTitle('Livre de test')
            ->setIsbn($this->randomIsbn())
            ->setType('TEXTE')
            ->setFilePath('fake.txt')
            ->setCreatedAt(new \DateTimeImmutable())
            ->setAuthor($auteur)
            ->setCategory($category);

        $em->persist($category);
        $em->persist($book);
        $em->flush();

        $client->loginUser($admin);

        $crawler = $client->request('GET', '/admin/users');
        $form = $crawler->filter('form[action="/admin/users/' . $auteur->getId() . '"]')->form();

        $client->submit($form);

        $this->assertResponseRedirects('/admin/users');
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-danger', 'auteur de');
    }

    private function randomIsbn(): string
    {
        // Format court, garanti < 20 caractères (colonne isbn: length 20)
        return sprintf('978-%d-%d', random_int(10, 99), random_int(100000, 999999));
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