<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240702211919 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, price VARCHAR(255) NOT NULL, credit VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        // $this->addSql('ALTER TABLE import_header DROP INDEX IDX_124312D9B6A263D9, ADD UNIQUE INDEX UNIQ_124312D9B6A263D9 (import_id)');
        // $this->addSql('ALTER TABLE user DROP INDEX IDX_8D93D649DF6E65AD, ADD UNIQUE INDEX UNIQ_8D93D649DF6E65AD (admin_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE product');
        // $this->addSql('ALTER TABLE import_header DROP INDEX UNIQ_124312D9B6A263D9, ADD INDEX IDX_124312D9B6A263D9 (import_id)');
        // $this->addSql('ALTER TABLE `user` DROP INDEX UNIQ_8D93D649DF6E65AD, ADD INDEX IDX_8D93D649DF6E65AD (admin_id_id)');
    }
}
