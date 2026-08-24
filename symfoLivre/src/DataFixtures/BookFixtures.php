<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\Category;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class BookFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $now       = new \DateTimeImmutable('now');
        $admin     = $this->getReference('user_admin', User::class);
        $auteur1   = $this->getReference('user_auteur1', User::class);
        $auteur2   = $this->getReference('user_auteur2', User::class);

        $catInfo   = $this->getReference('category_informatique', Category::class);
        $catBio    = $this->getReference('category_biologie', Category::class);
        $catSF     = $this->getReference('category_sci_fiction', Category::class);
        $catClass  = $this->getReference('category_roman_classique', Category::class);

        $booksData = [
            [
                'title'            => 'Les Algorithmes Quantiques',
                'isbn'             => '978-2-10-056789-1',
                'summary'          => 'Un tour d\'horizon des algorithmes exploitant la superposition et l\'intrication quantique pour résoudre des problèmes NP-complets.',
                'publicationDate'  => new \DateTime('2024-03-15'),
                'type'             => 'TEXTE',
                'filePath'         => 'uploads/books/algo_quantiques.md',
                'author'           => $auteur1,
                'category'         => $catInfo,
            ],
            [
                'title'            => 'Réseaux Neuronaux Profonds - Audio',
                'isbn'             => '978-2-10-067890-2',
                'summary'          => 'Cours audio complet sur les architectures CNN, RNN et Transformers, avec des exemples concrets en PyTorch.',
                'publicationDate'  => new \DateTime('2023-11-01'),
                'type'             => 'AUDIO',
                'filePath'         => 'uploads/books/resaux_neuronaux_audio.zip',
                'author'           => $auteur1,
                'category'         => $catInfo,
            ],
            [
                'title'            => 'Génétique Moléculaire et Épigenèse',
                'isbn'             => '978-2-10-078901-3',
                'summary'          => 'Comprendre les mécanismes d\'expression génique, la méthylation de l\'ADN et leur impact sur le développement.',
                'publicationDate'  => new \DateTime('2022-09-20'),
                'type'             => 'TEXTE',
                'filePath'         => 'uploads/books/genetique_moleculaire.txt',
                'author'           => $auteur2,
                'category'         => $catBio,
            ],
            [
                'title'            => 'Écologie des Récifs Coralliens',
                'isbn'             => '978-2-10-089012-4',
                'summary'          => 'Étude approfondie des écosystèmes coralliens face au réchauffement climatique et à l\'acidification des océans.',
                'publicationDate'  => new \DateTime('2023-06-10'),
                'type'             => 'AUDIO',
                'filePath'         => 'uploads/books/ecologie_recifs.zip',
                'author'           => $auteur2,
                'category'         => $catBio,
            ],
            [
                'title'            => 'Les Jardins de Néon',
                'isbn'             => '978-2-10-090123-5',
                'summary'          => 'Dans une mégalopole de 2147, une ingénieure en neuro-prothèses découvre un réseau de conscience collective caché sous les néons de la ville.',
                'publicationDate'  => new \DateTime('2024-01-08'),
                'type'             => 'TEXTE',
                'filePath'         => 'uploads/books/jardins_neon.md',
                'author'           => $auteur1,
                'category'         => $catSF,
            ],
            [
                'title'            => 'Exode Stellaire - Cycle III',
                'isbn'             => '978-2-10-001234-6',
                'summary'          => 'Les colons de la station Kepler-442b tentent de survivre à une éclipse solaire de 200 ans, seuls dans l\'espace profond.',
                'publicationDate'  => new \DateTime('2023-04-22'),
                'type'             => 'AUDIO',
                'filePath'         => 'uploads/books/exode_stellaire_c3.zip',
                'author'           => $auteur2,
                'category'         => $catSF,
            ],
            [
                'title'            => 'Le Comte de Monte-Cristo',
                'isbn'             => '978-2-10-012345-7',
                'summary'          => 'L\'histoire d\'Edmond Dantès, jeune marin trahi et emprisonné, qui s\'évade et se venge de ses trois bourreaux avec une patience diabolique.',
                'publicationDate'  => new \DateTime('1844-01-01'),
                'type'             => 'TEXTE',
                'filePath'         => 'uploads/books/monte_cristo.txt',
                'author'           => $admin,
                'category'         => $catClass,
            ],
            [
                'title'            => 'Les Misérables - Édition Intégrale',
                'isbn'             => '978-2-10-023456-8',
                'summary'          => 'L\'odyssée de Jean Valjean, de l\'emprisonnement à la rédemption, traversant la France de 1815 à 1832.',
                'publicationDate'  => new \DateTime('1862-04-30'),
                'type'             => 'TEXTE',
                'filePath'         => 'uploads/books/miserables.txt',
                'author'           => $admin,
                'category'         => $catClass,
            ],
        ];

        foreach ($booksData as $i => $data) {
            $book = new Book();
            $book->setTitle($data['title']);
            $book->setIsbn($data['isbn']);
            $book->setSummary($data['summary']);
            $book->setPublicationDate($data['publicationDate']);
            $book->setType($data['type']);
            $book->setFilePath($data['filePath']);
            $book->setAuthor($data['author']);
            $book->setCategory($data['category']);
            $book->setCreatedAt($now);
            $book->setUpdatedAt($now);
            $manager->persist($book);
            $this->addReference('book_' . ($i + 1), $book);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            UserFixtures::class,
        ];
    }
}