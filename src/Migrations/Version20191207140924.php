<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191207140924 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE device (id INT AUTO_INCREMENT NOT NULL, pole_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, type SMALLINT DEFAULT NULL, comment LONGTEXT DEFAULT NULL, INDEX IDX_92FB68E419C3385 (pole_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE note (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, support_group_id INT NOT NULL, support_person_id INT DEFAULT NULL, user_id INT NOT NULL, title VARCHAR(255) DEFAULT NULL, content LONGTEXT NOT NULL, type SMALLINT DEFAULT NULL, status SMALLINT DEFAULT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_CFBDFA14B03A8386 (created_by_id), INDEX IDX_CFBDFA14896DBBDE (updated_by_id), INDEX IDX_CFBDFA144AE25278 (support_group_id), INDEX IDX_CFBDFA1497A920F1 (support_person_id), INDEX IDX_CFBDFA14A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rdv (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, support_group_id INT NOT NULL, user_id INT NOT NULL, title VARCHAR(255) DEFAULT NULL, start_date DATE NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, status SMALLINT NOT NULL, location VARCHAR(255) DEFAULT NULL, content LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_10C31F86B03A8386 (created_by_id), INDEX IDX_10C31F86896DBBDE (updated_by_id), INDEX IDX_10C31F864AE25278 (support_group_id), INDEX IDX_10C31F86A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE referent (id INT AUTO_INCREMENT NOT NULL, group_people_id INT NOT NULL, name VARCHAR(255) NOT NULL, type SMALLINT NOT NULL, social_worker VARCHAR(100) DEFAULT NULL, email VARCHAR(100) DEFAULT NULL, phone1 VARCHAR(10) DEFAULT NULL, phone2 VARCHAR(10) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, departement VARCHAR(10) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, INDEX IDX_FE9AAC6CF1F495D7 (group_people_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE space (id INT AUTO_INCREMENT NOT NULL, service_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, space_number INT DEFAULT NULL, city VARCHAR(100) DEFAULT NULL, department VARCHAR(10) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, INDEX IDX_2972C13AED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE device ADD CONSTRAINT FK_92FB68E419C3385 FOREIGN KEY (pole_id) REFERENCES pole (id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA144AE25278 FOREIGN KEY (support_group_id) REFERENCES support_group (id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA1497A920F1 FOREIGN KEY (support_person_id) REFERENCES support_person (id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_CFBDFA14A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE rdv ADD CONSTRAINT FK_10C31F86B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE rdv ADD CONSTRAINT FK_10C31F86896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE rdv ADD CONSTRAINT FK_10C31F864AE25278 FOREIGN KEY (support_group_id) REFERENCES support_group (id)');
        $this->addSql('ALTER TABLE rdv ADD CONSTRAINT FK_10C31F86A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE referent ADD CONSTRAINT FK_FE9AAC6CF1F495D7 FOREIGN KEY (group_people_id) REFERENCES group_people (id)');
        $this->addSql('ALTER TABLE space ADD CONSTRAINT FK_2972C13AED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE device');
        $this->addSql('DROP TABLE note');
        $this->addSql('DROP TABLE rdv');
        $this->addSql('DROP TABLE referent');
        $this->addSql('DROP TABLE space');
    }
}
