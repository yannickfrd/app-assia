<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190913093818 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE group_people (id INT AUTO_INCREMENT NOT NULL, family_typology INT NOT NULL, nb_people INT NOT NULL, creation_date DATETIME NOT NULL, comment LONGTEXT DEFAULT NULL, update_date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE person (id INT AUTO_INCREMENT NOT NULL, lastname VARCHAR(50) NOT NULL, firstname VARCHAR(50) NOT NULL, usename VARCHAR(50) DEFAULT NULL, birthdate DATETIME DEFAULT NULL, gender SMALLINT DEFAULT NULL, nationality SMALLINT DEFAULT NULL, phone1 VARCHAR(20) DEFAULT NULL, phone2 VARCHAR(20) DEFAULT NULL, mail VARCHAR(100) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, creation_date DATETIME DEFAULT NULL, create_by VARCHAR(50) DEFAULT NULL, update_date DATETIME DEFAULT NULL, update_by VARCHAR(50) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role_person (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, group_people_id INT DEFAULT NULL, head TINYINT(1) NOT NULL, role SMALLINT NOT NULL, INDEX IDX_9FFA30C7217BBB47 (person_id), INDEX IDX_9FFA30C7F1F495D7 (group_people_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE social_support (id INT AUTO_INCREMENT NOT NULL, group_people_id INT NOT NULL, beginning_date DATE NOT NULL, end_date DATE DEFAULT NULL, status INT NOT NULL, comment LONGTEXT DEFAULT NULL, creation_date DATETIME NOT NULL, update_date DATETIME NOT NULL, INDEX IDX_F7F3E38F1F495D7 (group_people_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE role_person ADD CONSTRAINT FK_9FFA30C7217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE role_person ADD CONSTRAINT FK_9FFA30C7F1F495D7 FOREIGN KEY (group_people_id) REFERENCES group_people (id)');
        $this->addSql('ALTER TABLE social_support ADD CONSTRAINT FK_F7F3E38F1F495D7 FOREIGN KEY (group_people_id) REFERENCES group_people (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE role_person DROP FOREIGN KEY FK_9FFA30C7F1F495D7');
        $this->addSql('ALTER TABLE social_support DROP FOREIGN KEY FK_F7F3E38F1F495D7');
        $this->addSql('ALTER TABLE role_person DROP FOREIGN KEY FK_9FFA30C7217BBB47');
        $this->addSql('DROP TABLE group_people');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE role_person');
        $this->addSql('DROP TABLE social_support');
    }
}
