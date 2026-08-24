<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $categories = [
            [
                'label'       => 'Informatique',
                'description' => 'Programmation, systèmes, réseaux, IA et technologies numériques.',
                'ref'         => 'category_informatique',
            ],
            [
                'label'       => 'Biologie',
                'description' => 'Sciences du vivant, génétique, écologie et anatomie.',
                'ref'         => 'category_biologie',
            ],
            [
                'label'       => 'Science-Fiction',
                'description' => 'Roman d\'anticipation, uchronie, space opera et dystopies.',
                'ref'         => 'category_sci_fiction',
            ],
            [
                'label'       => 'Roman Classique',
                'description' => 'Grande littérature du XIXe et début du XXe siècle.',
                'ref'         => 'category_roman_classique',
            ],
        ];

        foreach ($categories as $data) {
            $category = new Category();
            $category->setLabel($data['label']);
            $category->setDescription($data['description']);
            $manager->persist($category);
            $this->addReference($data['ref'], $category);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [];
    }
}