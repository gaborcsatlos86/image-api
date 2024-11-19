<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241119133052 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE media_object_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE media_variants_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE media_object (id INT NOT NULL, file_path VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE media_variants (id INT NOT NULL, media_id INT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, file_path VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BCF97D53EA9FDD75 ON media_variants (media_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_media_type ON media_variants (media_id, type)');
        $this->addSql('ALTER TABLE media_variants ADD CONSTRAINT FK_BCF97D53EA9FDD75 FOREIGN KEY (media_id) REFERENCES media_object (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE media_object_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE media_variants_id_seq CASCADE');
        $this->addSql('ALTER TABLE media_variants DROP CONSTRAINT FK_BCF97D53EA9FDD75');
        $this->addSql('DROP TABLE media_object');
        $this->addSql('DROP TABLE media_variants');
    }
}
