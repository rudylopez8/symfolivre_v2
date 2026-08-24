<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $now = new \DateTimeImmutable('now');

        // ─── 1 Admin ───────────────────────────────────────────────
        $admin = new User();
        $admin->setEmail('admin@symfolivre.local');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'AdminPass123!'));
        $admin->setFirstname('Alice');
        $admin->setLastname('Dupont');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setCreatedAt($now);
        $admin->setUpdatedAt($now);
        $manager->persist($admin);
        $this->addReference('user_admin', $admin);

        // ─── 2 Auteurs ─────────────────────────────────────────────
        $auteur1 = new User();
        $auteur1->setEmail('auteur1@symfolivre.local');
        $auteur1->setPassword($this->passwordHasher->hashPassword($auteur1, 'AuteurPass123!'));
        $auteur1->setFirstname('Bernard');
        $auteur1->setLastname('Martin');
        $auteur1->setRoles(['ROLE_AUTEUR']);
        $auteur1->setCreatedAt($now);
        $auteur1->setUpdatedAt($now);
        $manager->persist($auteur1);
        $this->addReference('user_auteur1', $auteur1);

        $auteur2 = new User();
        $auteur2->setEmail('auteur2@symfolivre.local');
        $auteur2->setPassword($this->passwordHasher->hashPassword($auteur2, 'AuteurPass123!'));
        $auteur2->setFirstname('Claire');
        $auteur2->setLastname('Lefevre');
        $auteur2->setRoles(['ROLE_AUTEUR']);
        $auteur2->setCreatedAt($now);
        $auteur2->setUpdatedAt($now);
        $manager->persist($auteur2);
        $this->addReference('user_auteur2', $auteur2);

        // ─── 3 Lecteurs ────────────────────────────────────────────
        $lecteurs = [
            ['email' => 'lecteur1@symfolivre.local', 'firstname' => 'David', 'lastname' => 'Bernard'],
            ['email' => 'lecteur2@symfolivre.local', 'firstname' => 'Emma',  'lastname' => 'Petit'],
            ['email' => 'lecteur3@symfolivre.local', 'firstname' => 'Fabien','lastname' => 'Moreau'],
        ];

        foreach ($lecteurs as $i => $data) {
            $user = new User();
            $user->setEmail($data['email']);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'LecteurPass123!'));
            $user->setFirstname($data['firstname']);
            $user->setLastname($data['lastname']);
            $user->setRoles(['ROLE_USER']);
            $user->setCreatedAt($now);
            $user->setUpdatedAt($now);
            $manager->persist($user);
            $this->addReference('user_lecteur' . ($i + 1), $user);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [];
    }
}