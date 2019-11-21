<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191121212105 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE social_support_pers DROP FOREIGN KEY FK_D4D772D5AA309C73');
        $this->addSql('CREATE TABLE sit_family_grp (id INT AUTO_INCREMENT NOT NULL, support_grp_id INT DEFAULT NULL, nb_dependent_children SMALLINT DEFAULT NULL, unborn_child SMALLINT DEFAULT NULL, exp_date_childbirth DATE DEFAULT NULL, pregnancy_type SMALLINT DEFAULT NULL, faml_reunification SMALLINT DEFAULT NULL, nb_people_reunification SMALLINT DEFAULT NULL, caf_id VARCHAR(255) DEFAULT NULL, comment_sit_family_grp LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_D8F6148C5DE471A0 (support_grp_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sit_family_pers (id INT AUTO_INCREMENT NOT NULL, support_pers_id INT DEFAULT NULL, marital_status SMALLINT DEFAULT NULL, childcare_school SMALLINT DEFAULT NULL, childcare_school_location VARCHAR(255) DEFAULT NULL, child_to_host SMALLINT DEFAULT NULL, child_dependance SMALLINT DEFAULT NULL, UNIQUE INDEX UNIQ_19494E45F79039BF (support_pers_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sit_prof (id INT AUTO_INCREMENT NOT NULL, support_pers_id INT DEFAULT NULL, prof_status SMALLINT DEFAULT NULL, school_level SMALLINT DEFAULT NULL, job_type VARCHAR(255) DEFAULT NULL, contract_type SMALLINT DEFAULT NULL, contract_start_date DATE DEFAULT NULL, contract_end_date DATE DEFAULT NULL, nb_working_hours VARCHAR(50) DEFAULT NULL, working_hours VARCHAR(100) DEFAULT NULL, work_place VARCHAR(100) DEFAULT NULL, employer_name VARCHAR(100) DEFAULT NULL, transport_means VARCHAR(255) DEFAULT NULL, rqth SMALLINT DEFAULT NULL, comment_sit_prof LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_9DDF846F79039BF (support_pers_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sit_social (id INT AUTO_INCREMENT NOT NULL, support_grp_id INT DEFAULT NULL, reason_request SMALLINT DEFAULT NULL, wandering_time SMALLINT DEFAULT NULL, spe_animal TINYINT(1) DEFAULT NULL, spe_animal_name VARCHAR(255) DEFAULT NULL, spe_wheelchair TINYINT(1) DEFAULT NULL, spe_reduced_mobility TINYINT(1) DEFAULT NULL, spe_violence_victim TINYINT(1) DEFAULT NULL, spe_dom_violence_victim TINYINT(1) DEFAULT NULL, spe_ase_support TINYINT(1) DEFAULT NULL, spe_other TINYINT(1) DEFAULT NULL, spe_other_precision VARCHAR(255) DEFAULT NULL, spe_comment LONGTEXT DEFAULT NULL, comment_sit_social LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_331027025DE471A0 (support_grp_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE support_grp (id INT AUTO_INCREMENT NOT NULL, group_people_id INT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, service_id INT DEFAULT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, status INT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_E468E757F1F495D7 (group_people_id), INDEX IDX_E468E757B03A8386 (created_by_id), INDEX IDX_E468E757896DBBDE (updated_by_id), INDEX IDX_E468E757ED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE support_pers (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, support_grp_id INT NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, status INT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_874DBEA217BBB47 (person_id), INDEX IDX_874DBEA5DE471A0 (support_grp_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sit_family_grp ADD CONSTRAINT FK_D8F6148C5DE471A0 FOREIGN KEY (support_grp_id) REFERENCES support_grp (id)');
        $this->addSql('ALTER TABLE sit_family_pers ADD CONSTRAINT FK_19494E45F79039BF FOREIGN KEY (support_pers_id) REFERENCES support_pers (id)');
        $this->addSql('ALTER TABLE sit_prof ADD CONSTRAINT FK_9DDF846F79039BF FOREIGN KEY (support_pers_id) REFERENCES support_pers (id)');
        $this->addSql('ALTER TABLE sit_social ADD CONSTRAINT FK_331027025DE471A0 FOREIGN KEY (support_grp_id) REFERENCES support_grp (id)');
        $this->addSql('ALTER TABLE support_grp ADD CONSTRAINT FK_E468E757F1F495D7 FOREIGN KEY (group_people_id) REFERENCES group_people (id)');
        $this->addSql('ALTER TABLE support_grp ADD CONSTRAINT FK_E468E757B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE support_grp ADD CONSTRAINT FK_E468E757896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE support_grp ADD CONSTRAINT FK_E468E757ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('ALTER TABLE support_pers ADD CONSTRAINT FK_874DBEA217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE support_pers ADD CONSTRAINT FK_874DBEA5DE471A0 FOREIGN KEY (support_grp_id) REFERENCES support_grp (id)');
        $this->addSql('DROP TABLE social_support_grp');
        $this->addSql('DROP TABLE social_support_pers');
        $this->addSql('ALTER TABLE person ADD maiden_name VARCHAR(100) DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sit_family_grp DROP FOREIGN KEY FK_D8F6148C5DE471A0');
        $this->addSql('ALTER TABLE sit_social DROP FOREIGN KEY FK_331027025DE471A0');
        $this->addSql('ALTER TABLE support_pers DROP FOREIGN KEY FK_874DBEA5DE471A0');
        $this->addSql('ALTER TABLE sit_family_pers DROP FOREIGN KEY FK_19494E45F79039BF');
        $this->addSql('ALTER TABLE sit_prof DROP FOREIGN KEY FK_9DDF846F79039BF');
        $this->addSql('CREATE TABLE social_support_grp (id INT AUTO_INCREMENT NOT NULL, group_people_id INT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, service_id INT DEFAULT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, status INT NOT NULL, comment LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_91CC1779ED5CA9E6 (service_id), INDEX IDX_91CC1779896DBBDE (updated_by_id), INDEX IDX_91CC1779F1F495D7 (group_people_id), INDEX IDX_91CC1779B03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE social_support_pers (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, social_support_grp_id INT NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, status INT NOT NULL, comment LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_D4D772D5217BBB47 (person_id), INDEX IDX_D4D772D5AA309C73 (social_support_grp_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE social_support_grp ADD CONSTRAINT FK_91CC1779896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE social_support_grp ADD CONSTRAINT FK_91CC1779B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE social_support_grp ADD CONSTRAINT FK_91CC1779ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('ALTER TABLE social_support_grp ADD CONSTRAINT FK_91CC1779F1F495D7 FOREIGN KEY (group_people_id) REFERENCES group_people (id)');
        $this->addSql('ALTER TABLE social_support_pers ADD CONSTRAINT FK_D4D772D5217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE social_support_pers ADD CONSTRAINT FK_D4D772D5AA309C73 FOREIGN KEY (social_support_grp_id) REFERENCES social_support_grp (id)');
        $this->addSql('DROP TABLE sit_family_grp');
        $this->addSql('DROP TABLE sit_family_pers');
        $this->addSql('DROP TABLE sit_prof');
        $this->addSql('DROP TABLE sit_social');
        $this->addSql('DROP TABLE support_grp');
        $this->addSql('DROP TABLE support_pers');
        $this->addSql('ALTER TABLE person DROP maiden_name');
    }
}
