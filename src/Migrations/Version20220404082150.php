<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220404082150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_taxon ADD breadcrumb TINYINT(1) DEFAULT NULL, CHANGE tags tags VARCHAR(125) DEFAULT NULL');
        $this->addSql('UPDATE sylius_taxon SET breadcrumb=true WHERE 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_taxon DROP breadcrumb, CHANGE tags tags VARCHAR(125) CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci`');
    }
}
