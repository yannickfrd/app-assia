<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191006212106 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE group_people ADD created_at DATETIME NOT NULL, ADD created_by INT DEFAULT NULL, ADD updated_at DATETIME NOT NULL, ADD updated_by INT DEFAULT NULL, DROP creation_date, DROP create_by, DROP update_date, DROP update_by');
        $this->addSql('ALTER TABLE person ADD created_at DATETIME DEFAULT NULL, ADD created_by INT DEFAULT NULL, ADD updated_at DATETIME DEFAULT NULL, ADD updated_by INT DEFAULT NULL, DROP creation_date, DROP create_by, DROP update_date, DROP update_by');
        $this->addSql('ALTER TABLE role_person CHANGE head head TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE social_support ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME NOT NULL, DROP creation_date, DROP update_date');
        $this->addSql('ALTER TABLE user CHANGE creation_date created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE group_people ADD creation_date DATETIME NOT NULL, ADD create_by INT DEFAULT NULL, ADD update_date DATETIME NOT NULL, ADD update_by INT DEFAULT NULL, DROP created_at, DROP created_by, DROP updated_at, DROP updated_by');
        $this->addSql('ALTER TABLE person ADD creation_date DATETIME DEFAULT NULL, ADD create_by INT DEFAULT NULL, ADD update_date DATETIME DEFAULT NULL, ADD update_by INT DEFAULT NULL, DROP created_at, DROP created_by, DROP updated_at, DROP updated_by');
        $this->addSql('ALTER TABLE role_person CHANGE head head TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE social_support ADD creation_date DATETIME NOT NULL, ADD update_date DATETIME NOT NULL, DROP created_at, DROP updated_at');
        $this->addSql('ALTER TABLE user CHANGE created_at creation_date DATETIME NOT NULL');
    }
}
