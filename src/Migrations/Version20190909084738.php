<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190909084738 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE group_person');
        $this->addSql('ALTER TABLE `group` DROP relation, CHANGE family_typology family_typology INT NOT NULL, CHANGE nb_people nb_people INT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE group_person (group_id INT NOT NULL, person_id INT NOT NULL, INDEX IDX_E75A09A6FE54D947 (group_id), INDEX IDX_E75A09A6217BBB47 (person_id), PRIMARY KEY(group_id, person_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE group_person ADD CONSTRAINT FK_E75A09A6217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE group_person ADD CONSTRAINT FK_E75A09A6FE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `group` ADD relation VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, CHANGE family_typology family_typology INT DEFAULT NULL, CHANGE nb_people nb_people INT DEFAULT NULL');
    }
}
