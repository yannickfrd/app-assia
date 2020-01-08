<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200108114721 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user CHANGE failure_login_count failure_login_count INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE person ADD contact_other_person VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE rdv CHANGE status status SMALLINT DEFAULT 0, CHANGE title title VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE person DROP contact_other_person');
        $this->addSql('ALTER TABLE rdv CHANGE title title VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE status status SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE failure_login_count failure_login_count INT DEFAULT NULL');
    }
}
