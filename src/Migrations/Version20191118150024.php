<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191118150024 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE group_people (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, family_typology SMALLINT NOT NULL, nb_people SMALLINT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_FB90B2F6B03A8386 (created_by_id), INDEX IDX_FB90B2F6896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE person (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, lastname VARCHAR(50) NOT NULL, firstname VARCHAR(50) NOT NULL, usename VARCHAR(50) DEFAULT NULL, birthdate DATETIME DEFAULT NULL, gender SMALLINT NOT NULL, phone1 VARCHAR(20) DEFAULT NULL, phone2 VARCHAR(20) DEFAULT NULL, email VARCHAR(100) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_34DCD176B03A8386 (created_by_id), INDEX IDX_34DCD176896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pole (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(100) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, zip_code VARCHAR(10) DEFAULT NULL, director VARCHAR(255) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, color VARCHAR(50) DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role_person (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, group_people_id INT DEFAULT NULL, head TINYINT(1) DEFAULT \'0\' NOT NULL, role SMALLINT NOT NULL, created_at DATETIME DEFAULT NULL, created_by INT DEFAULT NULL, INDEX IDX_9FFA30C7217BBB47 (person_id), INDEX IDX_9FFA30C7F1F495D7 (group_people_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role_user (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, service_id INT DEFAULT NULL, role INT NOT NULL, INDEX IDX_332CA4DDA76ED395 (user_id), INDEX IDX_332CA4DDED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service (id INT AUTO_INCREMENT NOT NULL, pole_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(100) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, zip_code VARCHAR(10) DEFAULT NULL, chief VARCHAR(255) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_E19D9AD2419C3385 (pole_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE social_support_grp (id INT AUTO_INCREMENT NOT NULL, group_people_id INT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, service_id INT DEFAULT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, status INT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_91CC1779F1F495D7 (group_people_id), INDEX IDX_91CC1779B03A8386 (created_by_id), INDEX IDX_91CC1779896DBBDE (updated_by_id), INDEX IDX_91CC1779ED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE social_support_pers (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, social_support_grp_id INT NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, status INT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_D4D772D5217BBB47 (person_id), INDEX IDX_D4D772D5AA309C73 (social_support_grp_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(50) NOT NULL, email VARCHAR(100) NOT NULL, phone VARCHAR(20) DEFAULT NULL, password VARCHAR(255) NOT NULL, lastname VARCHAR(50) NOT NULL, firstname VARCHAR(50) NOT NULL, roles JSON NOT NULL, created_at DATETIME NOT NULL, login_count INT DEFAULT 0 NOT NULL, last_login DATETIME DEFAULT NULL, failure_login_count INT DEFAULT NULL, token VARCHAR(255) DEFAULT NULL, token_created_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_connection (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, connection_at DATETIME NOT NULL, INDEX IDX_8E90B58AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE group_people ADD CONSTRAINT FK_FB90B2F6B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE group_people ADD CONSTRAINT FK_FB90B2F6896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD176B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD176896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE role_person ADD CONSTRAINT FK_9FFA30C7217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE role_person ADD CONSTRAINT FK_9FFA30C7F1F495D7 FOREIGN KEY (group_people_id) REFERENCES group_people (id)');
        $this->addSql('ALTER TABLE role_user ADD CONSTRAINT FK_332CA4DDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE role_user ADD CONSTRAINT FK_332CA4DDED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD2419C3385 FOREIGN KEY (pole_id) REFERENCES pole (id)');
        $this->addSql('ALTER TABLE social_support_grp ADD CONSTRAINT FK_91CC1779F1F495D7 FOREIGN KEY (group_people_id) REFERENCES group_people (id)');
        $this->addSql('ALTER TABLE social_support_grp ADD CONSTRAINT FK_91CC1779B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE social_support_grp ADD CONSTRAINT FK_91CC1779896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE social_support_grp ADD CONSTRAINT FK_91CC1779ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('ALTER TABLE social_support_pers ADD CONSTRAINT FK_D4D772D5217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE social_support_pers ADD CONSTRAINT FK_D4D772D5AA309C73 FOREIGN KEY (social_support_grp_id) REFERENCES social_support_grp (id)');
        $this->addSql('ALTER TABLE user_connection ADD CONSTRAINT FK_8E90B58AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE role_person DROP FOREIGN KEY FK_9FFA30C7F1F495D7');
        $this->addSql('ALTER TABLE social_support_grp DROP FOREIGN KEY FK_91CC1779F1F495D7');
        $this->addSql('ALTER TABLE role_person DROP FOREIGN KEY FK_9FFA30C7217BBB47');
        $this->addSql('ALTER TABLE social_support_pers DROP FOREIGN KEY FK_D4D772D5217BBB47');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD2419C3385');
        $this->addSql('ALTER TABLE role_user DROP FOREIGN KEY FK_332CA4DDED5CA9E6');
        $this->addSql('ALTER TABLE social_support_grp DROP FOREIGN KEY FK_91CC1779ED5CA9E6');
        $this->addSql('ALTER TABLE social_support_pers DROP FOREIGN KEY FK_D4D772D5AA309C73');
        $this->addSql('ALTER TABLE group_people DROP FOREIGN KEY FK_FB90B2F6B03A8386');
        $this->addSql('ALTER TABLE group_people DROP FOREIGN KEY FK_FB90B2F6896DBBDE');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176B03A8386');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176896DBBDE');
        $this->addSql('ALTER TABLE role_user DROP FOREIGN KEY FK_332CA4DDA76ED395');
        $this->addSql('ALTER TABLE social_support_grp DROP FOREIGN KEY FK_91CC1779B03A8386');
        $this->addSql('ALTER TABLE social_support_grp DROP FOREIGN KEY FK_91CC1779896DBBDE');
        $this->addSql('ALTER TABLE user_connection DROP FOREIGN KEY FK_8E90B58AA76ED395');
        $this->addSql('DROP TABLE group_people');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE pole');
        $this->addSql('DROP TABLE role_person');
        $this->addSql('DROP TABLE role_user');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE social_support_grp');
        $this->addSql('DROP TABLE social_support_pers');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_connection');
    }
}
