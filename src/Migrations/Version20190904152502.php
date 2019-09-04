<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190904152502 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE `group` (id INT AUTO_INCREMENT NOT NULL, family_typology VARCHAR(50) NOT NULL, nbr_people INT NOT NULL, comment LONGTEXT DEFAULT NULL, creation_date DATETIME NOT NULL, update_date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE group_person (group_id INT NOT NULL, person_id INT NOT NULL, INDEX IDX_E75A09A6FE54D947 (group_id), INDEX IDX_E75A09A6217BBB47 (person_id), PRIMARY KEY(group_id, person_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE group_person ADD CONSTRAINT FK_E75A09A6FE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE group_person ADD CONSTRAINT FK_E75A09A6217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE person CHANGE creation_date creation_date DATETIME DEFAULT NULL, CHANGE update_date update_date DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE group_person DROP FOREIGN KEY FK_E75A09A6FE54D947');
        $this->addSql('DROP TABLE `group`');
        $this->addSql('DROP TABLE group_person');
        $this->addSql('ALTER TABLE person CHANGE creation_date creation_date DATETIME NOT NULL, CHANGE update_date update_date DATETIME NOT NULL');
    }
}
