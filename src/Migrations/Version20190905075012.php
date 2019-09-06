<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190905075012 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE social_support (id INT AUTO_INCREMENT NOT NULL, group_people_id INT NOT NULL, beginning_date DATETIME NOT NULL, end_date DATETIME DEFAULT NULL, status VARCHAR(50) NOT NULL, comment LONGTEXT DEFAULT NULL, creation_date DATETIME NOT NULL, create_by INT DEFAULT NULL, update_date DATETIME NOT NULL, update_by INT DEFAULT NULL, INDEX IDX_F7F3E38F1F495D7 (group_people_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE social_support ADD CONSTRAINT FK_F7F3E38F1F495D7 FOREIGN KEY (group_people_id) REFERENCES `group` (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE social_support');
    }
}
