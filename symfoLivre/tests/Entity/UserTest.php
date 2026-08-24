<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetUserIdentifierReturnsEmail(): void
    {
        $user = new User();
        $user->setEmail('lecteur@example.com');

        $this->assertSame('lecteur@example.com', $user->getUserIdentifier());
    }

    public function testGetRolesAlwaysIncludesRoleUser(): void
    {
        $user = new User();
        $user->setRoles([]);

        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testGetRolesDoesNotDuplicateRoleUser(): void
    {
        $user = new User();
        $user->setRoles(['ROLE_USER', 'ROLE_AUTEUR']);

        $this->assertCount(2, $user->getRoles());
        $this->assertContains('ROLE_AUTEUR', $user->getRoles());
    }

    public function testFluentSetters(): void
    {
        $user = (new User())
            ->setEmail('auteur@example.com')
            ->setFirstname('Jean')
            ->setLastname('Dupont');

        $this->assertSame('Jean', $user->getFirstname());
        $this->assertSame('Dupont', $user->getLastname());
        $this->assertSame('auteur@example.com', $user->getEmail());
    }
}