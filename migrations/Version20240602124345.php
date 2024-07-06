<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240602124345 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE import_header DROP INDEX IDX_124312D9B6A263D9, ADD UNIQUE INDEX UNIQ_124312D9B6A263D9 (import_id)');
        $this->addSql('ALTER TABLE import_header ADD is_exportable TINYINT(1) NOT NULL, ADD is_displayable TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // $this->addSql('ALTER TABLE import_header DROP INDEX UNIQ_124312D9B6A263D9, ADD INDEX IDX_124312D9B6A263D9 (import_id)');
        $this->addSql('ALTER TABLE import_header DROP is_exportable, DROP is_displayable');
    }
}
