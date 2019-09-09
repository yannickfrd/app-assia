<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190909085757 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE people_group_person (people_group_id INT NOT NULL, person_id INT NOT NULL, INDEX IDX_763636E28FA5F5D2 (people_group_id), INDEX IDX_763636E2217BBB47 (person_id), PRIMARY KEY(people_group_id, person_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE people_group_person ADD CONSTRAINT FK_763636E28FA5F5D2 FOREIGN KEY (people_group_id) REFERENCES people_group (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE people_group_person ADD CONSTRAINT FK_763636E2217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE people_group_person');
    }
}
