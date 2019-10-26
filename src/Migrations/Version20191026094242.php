<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191026094242 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE department ADD email VARCHAR(100) DEFAULT NULL, ADD phone VARCHAR(20) DEFAULT NULL, ADD address VARCHAR(255) DEFAULT NULL, ADD city VARCHAR(255) DEFAULT NULL, ADD zip_code VARCHAR(10) DEFAULT NULL, ADD chief VARCHAR(255) DEFAULT NULL, ADD comment LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE pole ADD email VARCHAR(100) DEFAULT NULL, ADD phone VARCHAR(20) DEFAULT NULL, ADD address VARCHAR(255) DEFAULT NULL, ADD city VARCHAR(255) DEFAULT NULL, ADD zip_code VARCHAR(10) DEFAULT NULL, ADD director VARCHAR(255) DEFAULT NULL, ADD comment LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE department DROP email, DROP phone, DROP address, DROP city, DROP zip_code, DROP chief, DROP comment');
        $this->addSql('ALTER TABLE pole DROP email, DROP phone, DROP address, DROP city, DROP zip_code, DROP director, DROP comment');
    }
}
