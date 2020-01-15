<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200114165320 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, group_people_id INT DEFAULT NULL, support_group_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, type SMALLINT DEFAULT NULL, content LONGTEXT DEFAULT NULL, internal_file_name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_D8698A76B03A8386 (created_by_id), INDEX IDX_D8698A76F1F495D7 (group_people_id), INDEX IDX_D8698A764AE25278 (support_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A76F1F495D7 FOREIGN KEY (group_people_id) REFERENCES group_people (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A764AE25278 FOREIGN KEY (support_group_id) REFERENCES support_group (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE document');
    }
}
