<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240228192425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE import (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, groups_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, file LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_9D4ECE1DA76ED395 (user_id), INDEX IDX_9D4ECE1DF373DCF (groups_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE import ADD CONSTRAINT FK_9D4ECE1DA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE import ADD CONSTRAINT FK_9D4ECE1DF373DCF FOREIGN KEY (groups_id) REFERENCES `group` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE import DROP FOREIGN KEY FK_9D4ECE1DA76ED395');
        $this->addSql('ALTER TABLE import DROP FOREIGN KEY FK_9D4ECE1DF373DCF');
        $this->addSql('DROP TABLE import');
    }
}
