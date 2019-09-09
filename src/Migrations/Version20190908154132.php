<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190908154132 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE person CHANGE phone1 phone1 VARCHAR(20) DEFAULT NULL, CHANGE phone2 phone2 VARCHAR(20) DEFAULT NULL, CHANGE sex gender VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE social_support CHANGE status status INT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE person CHANGE phone1 phone1 VARCHAR(15) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE phone2 phone2 VARCHAR(15) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE gender sex VARCHAR(10) DEFAULT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE social_support CHANGE status status VARCHAR(50) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
