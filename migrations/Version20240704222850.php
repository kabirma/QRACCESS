<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240704222850 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE transaction (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, product_id INT DEFAULT NULL, charge_id VARCHAR(255) NOT NULL, price VARCHAR(255) NOT NULL, credit VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_723705D1A76ED395 (user_id), UNIQUE INDEX UNIQ_723705D14584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D1A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE transaction ADD CONSTRAINT FK_723705D14584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        // $this->addSql('ALTER TABLE import_header DROP INDEX IDX_124312D9B6A263D9, ADD UNIQUE INDEX UNIQ_124312D9B6A263D9 (import_id)');
        // $this->addSql('ALTER TABLE user DROP INDEX IDX_8D93D649DF6E65AD, ADD UNIQUE INDEX UNIQ_8D93D649DF6E65AD (admin_id_id)');
        $this->addSql('ALTER TABLE user CHANGE credits credits INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D1A76ED395');
        $this->addSql('ALTER TABLE transaction DROP FOREIGN KEY FK_723705D14584665A');
        $this->addSql('DROP TABLE transaction');
        // $this->addSql('ALTER TABLE import_header DROP INDEX UNIQ_124312D9B6A263D9, ADD INDEX IDX_124312D9B6A263D9 (import_id)');
        // $this->addSql('ALTER TABLE `user` DROP INDEX UNIQ_8D93D649DF6E65AD, ADD INDEX IDX_8D93D649DF6E65AD (admin_id_id)');
        $this->addSql('ALTER TABLE `user` CHANGE credits credits INT DEFAULT 10 NOT NULL');
    }
}
