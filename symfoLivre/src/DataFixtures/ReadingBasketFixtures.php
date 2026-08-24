<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\ReadingBasket;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ReadingBasketFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $now       = new \DateTimeImmutable('now');
        $lecteur1  = $this->getReference('user_lecteur1', User::class);
        $lecteur2  = $this->getReference('user_lecteur2', User::class);

        // Lecteur 1 : 3 livres dans le panier
        $basketItems1 = [
            $this->getReference('book_1', Book::class),
            $this->getReference('book_5', Book::class),
            $this->getReference('book_7', Book::class),
        ];
        foreach ($basketItems1 as $book) {
            $entry = new ReadingBasket();
            $entry->setUser($lecteur1);
            $entry->setBook($book);
            $entry->setAddedAt($now);
            $manager->persist($entry);
        }

        // Lecteur 2 : 2 livres dans le panier
        $basketItems2 = [
            $this->getReference('book_4', Book::class),
            $this->getReference('book_8', Book::class),
        ];
        foreach ($basketItems2 as $book) {
            $entry = new ReadingBasket();
            $entry->setUser($lecteur2);
            $entry->setBook($book);
            $entry->setAddedAt($now);
            $manager->persist($entry);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            BookFixtures::class,
        ];
    }
}