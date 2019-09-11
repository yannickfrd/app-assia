<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190911095740 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE role_person (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, people_group_id INT DEFAULT NULL, head TINYINT(1) NOT NULL, role SMALLINT NOT NULL, INDEX IDX_9FFA30C7217BBB47 (person_id), INDEX IDX_9FFA30C78FA5F5D2 (people_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE role_person ADD CONSTRAINT FK_9FFA30C7217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE role_person ADD CONSTRAINT FK_9FFA30C78FA5F5D2 FOREIGN KEY (people_group_id) REFERENCES people_group (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE role_person');
    }
}
