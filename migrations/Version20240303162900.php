<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240303162900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE import_header (id INT AUTO_INCREMENT NOT NULL, import_id INT DEFAULT NULL, field LONGTEXT NOT NULL, created_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_124312D9B6A263D9 (import_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE import_row (id INT AUTO_INCREMENT NOT NULL, import_id INT DEFAULT NULL, header_id INT DEFAULT NULL, data LONGTEXT NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_105D565BB6A263D9 (import_id), INDEX IDX_105D565B2EF91FD8 (header_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE import_header ADD CONSTRAINT FK_124312D9B6A263D9 FOREIGN KEY (import_id) REFERENCES import (id)');
        $this->addSql('ALTER TABLE import_row ADD CONSTRAINT FK_105D565BB6A263D9 FOREIGN KEY (import_id) REFERENCES import (id)');
        $this->addSql('ALTER TABLE import_row ADD CONSTRAINT FK_105D565B2EF91FD8 FOREIGN KEY (header_id) REFERENCES import_header (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE import_header DROP FOREIGN KEY FK_124312D9B6A263D9');
        $this->addSql('ALTER TABLE import_row DROP FOREIGN KEY FK_105D565BB6A263D9');
        $this->addSql('ALTER TABLE import_row DROP FOREIGN KEY FK_105D565B2EF91FD8');
        $this->addSql('DROP TABLE import_header');
        $this->addSql('DROP TABLE import_row');
    }
}
