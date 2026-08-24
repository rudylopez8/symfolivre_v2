<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260819163207 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE book (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, isbn VARCHAR(20) NOT NULL, summary LONGTEXT DEFAULT NULL, publication_date DATE DEFAULT NULL, type VARCHAR(31) NOT NULL, file_path VARCHAR(511) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, author_id INT NOT NULL, category_id INT NOT NULL, UNIQUE INDEX UNIQ_CBE5A331CC1CF4E6 (isbn), INDEX IDX_CBE5A331F675F31B (author_id), INDEX IDX_CBE5A33112469DE2 (category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, label VARCHAR(127) NOT NULL, description LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_64C19C1EA750E8 (label), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reading_basket (id INT AUTO_INCREMENT NOT NULL, added_at DATETIME NOT NULL, user_id INT NOT NULL, book_id INT NOT NULL, INDEX IDX_BFF81617A76ED395 (user_id), INDEX IDX_BFF8161716A2B381 (book_id), UNIQUE INDEX uniq_user_book (user_id, book_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(31) NOT NULL, lastname VARCHAR(63) NOT NULL, roles JSON NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE book ADD CONSTRAINT FK_CBE5A331F675F31B FOREIGN KEY (author_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE book ADD CONSTRAINT FK_CBE5A33112469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE reading_basket ADD CONSTRAINT FK_BFF81617A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reading_basket ADD CONSTRAINT FK_BFF8161716A2B381 FOREIGN KEY (book_id) REFERENCES book (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book DROP FOREIGN KEY FK_CBE5A331F675F31B');
        $this->addSql('ALTER TABLE book DROP FOREIGN KEY FK_CBE5A33112469DE2');
        $this->addSql('ALTER TABLE reading_basket DROP FOREIGN KEY FK_BFF81617A76ED395');
        $this->addSql('ALTER TABLE reading_basket DROP FOREIGN KEY FK_BFF8161716A2B381');
        $this->addSql('DROP TABLE book');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE reading_basket');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
