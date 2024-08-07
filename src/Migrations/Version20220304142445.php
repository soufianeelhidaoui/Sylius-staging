<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220304142445 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE monsieurbiz_homepage_homepage (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE monsieurbiz_homepage_homepage_channels (page_id INT NOT NULL, channel_id INT NOT NULL, INDEX IDX_1BF0DD4EC4663E4 (page_id), UNIQUE INDEX UNIQ_1BF0DD4E72F5A1AA (channel_id), PRIMARY KEY(page_id, channel_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE monsieurbiz_homepage_homepage_translation (id INT AUTO_INCREMENT NOT NULL, translatable_id INT NOT NULL, name VARCHAR(255) DEFAULT NULL, content LONGTEXT DEFAULT NULL, metaTitle VARCHAR(255) DEFAULT NULL, metaKeywords VARCHAR(255) DEFAULT NULL, metaDescription LONGTEXT DEFAULT NULL, locale VARCHAR(10) DEFAULT NULL, INDEX IDX_F0076BB22C2AC5D3 (translatable_id), UNIQUE INDEX monsieurbiz_homepage_homepage_translation_uniq_trans (translatable_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE monsieurbiz_homepage_homepage_channels ADD CONSTRAINT FK_1BF0DD4EC4663E4 FOREIGN KEY (page_id) REFERENCES monsieurbiz_homepage_homepage (id)');
        $this->addSql('ALTER TABLE monsieurbiz_homepage_homepage_channels ADD CONSTRAINT FK_1BF0DD4E72F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id)');
        $this->addSql('ALTER TABLE monsieurbiz_homepage_homepage_translation ADD CONSTRAINT FK_F0076BB22C2AC5D3 FOREIGN KEY (translatable_id) REFERENCES monsieurbiz_homepage_homepage (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_adjustment CHANGE details details LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE sylius_gateway_config CHANGE config config LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE sylius_payment CHANGE details details LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE sylius_product_attribute_value CHANGE json_value json_value LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE monsieurbiz_homepage_homepage_channels DROP FOREIGN KEY FK_1BF0DD4EC4663E4');
        $this->addSql('ALTER TABLE monsieurbiz_homepage_homepage_translation DROP FOREIGN KEY FK_F0076BB22C2AC5D3');
        $this->addSql('DROP TABLE monsieurbiz_homepage_homepage');
        $this->addSql('DROP TABLE monsieurbiz_homepage_homepage_channels');
        $this->addSql('DROP TABLE monsieurbiz_homepage_homepage_translation');
        $this->addSql('ALTER TABLE sylius_adjustment CHANGE details details LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:json_array)\'');
        $this->addSql('ALTER TABLE sylius_gateway_config CHANGE config config LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:json_array)\'');
        $this->addSql('ALTER TABLE sylius_payment CHANGE details details LONGTEXT CHARACTER SET utf8 NOT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:json_array)\'');
        $this->addSql('ALTER TABLE sylius_product_attribute_value CHANGE json_value json_value LONGTEXT CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci` COMMENT \'(DC2Type:json_array)\'');
    }
}
