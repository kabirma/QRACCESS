<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240604204151 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD admin_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649DF6E65AD FOREIGN KEY (admin_id_id) REFERENCES `user` (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649DF6E65AD ON user (admin_id_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649DF6E65AD');
        $this->addSql('DROP INDEX UNIQ_8D93D649DF6E65AD ON `user`');
        $this->addSql('ALTER TABLE `user` DROP admin_id_id');
    }
}
