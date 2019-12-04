<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191204201058 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sit_budget_grp DROP FOREIGN KEY FK_B0DE52845DE471A0');
        $this->addSql('ALTER TABLE sit_family_grp DROP FOREIGN KEY FK_D8F6148C5DE471A0');
        $this->addSql('ALTER TABLE sit_housing DROP FOREIGN KEY FK_661144AA5DE471A0');
        $this->addSql('ALTER TABLE sit_social DROP FOREIGN KEY FK_331027025DE471A0');
        $this->addSql('ALTER TABLE support_pers DROP FOREIGN KEY FK_874DBEA5DE471A0');
        $this->addSql('ALTER TABLE sit_adm DROP FOREIGN KEY FK_8BAC00F0F79039BF');
        $this->addSql('ALTER TABLE sit_budget DROP FOREIGN KEY FK_318331FEF79039BF');
        $this->addSql('ALTER TABLE sit_family_pers DROP FOREIGN KEY FK_19494E45F79039BF');
        $this->addSql('ALTER TABLE sit_prof DROP FOREIGN KEY FK_9DDF846F79039BF');
        $this->addSql('CREATE TABLE support_group (id INT AUTO_INCREMENT NOT NULL, referent_id INT DEFAULT NULL, referent2_id INT DEFAULT NULL, group_people_id INT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, service_id INT DEFAULT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, status INT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_9F7A521D35E47E35 (referent_id), INDEX IDX_9F7A521D925FA220 (referent2_id), INDEX IDX_9F7A521DF1F495D7 (group_people_id), INDEX IDX_9F7A521DB03A8386 (created_by_id), INDEX IDX_9F7A521D896DBBDE (updated_by_id), INDEX IDX_9F7A521DED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE support_person (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, support_group_id INT NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, status INT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_BC263186217BBB47 (person_id), INDEX IDX_BC2631864AE25278 (support_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sit_budget_group (id INT AUTO_INCREMENT NOT NULL, support_group_id INT NOT NULL, ressources_group_amt INT DEFAULT NULL, charges_group_amt INT DEFAULT NULL, debts_group_amt INT DEFAULT NULL, tax_income_n1amt INT DEFAULT NULL, tax_income_n2amt INT DEFAULT NULL, monthly_repayment_amt DOUBLE PRECISION DEFAULT NULL, budget_balance_amt INT DEFAULT NULL, comment_sit_budget LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_8E64D7744AE25278 (support_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sit_family_group (id INT AUTO_INCREMENT NOT NULL, support_group_id INT DEFAULT NULL, nb_dependent_children SMALLINT DEFAULT NULL, unborn_child SMALLINT DEFAULT NULL, exp_date_childbirth DATE DEFAULT NULL, pregnancy_type SMALLINT DEFAULT NULL, faml_reunification SMALLINT DEFAULT NULL, nb_people_reunification SMALLINT DEFAULT NULL, caf_id VARCHAR(255) DEFAULT NULL, comment_sit_family_group LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_D902D1F14AE25278 (support_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sit_family_person (id INT AUTO_INCREMENT NOT NULL, support_person_id INT NOT NULL, marital_status SMALLINT DEFAULT NULL, childcare_school SMALLINT DEFAULT NULL, childcare_school_location VARCHAR(255) DEFAULT NULL, child_to_host SMALLINT DEFAULT NULL, child_dependance SMALLINT DEFAULT NULL, UNIQUE INDEX UNIQ_15DCE75697A920F1 (support_person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE support_group ADD CONSTRAINT FK_9F7A521D35E47E35 FOREIGN KEY (referent_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE support_group ADD CONSTRAINT FK_9F7A521D925FA220 FOREIGN KEY (referent2_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE support_group ADD CONSTRAINT FK_9F7A521DF1F495D7 FOREIGN KEY (group_people_id) REFERENCES group_people (id)');
        $this->addSql('ALTER TABLE support_group ADD CONSTRAINT FK_9F7A521DB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE support_group ADD CONSTRAINT FK_9F7A521D896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE support_group ADD CONSTRAINT FK_9F7A521DED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('ALTER TABLE support_person ADD CONSTRAINT FK_BC263186217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE support_person ADD CONSTRAINT FK_BC2631864AE25278 FOREIGN KEY (support_group_id) REFERENCES support_group (id)');
        $this->addSql('ALTER TABLE sit_budget_group ADD CONSTRAINT FK_8E64D7744AE25278 FOREIGN KEY (support_group_id) REFERENCES support_group (id)');
        $this->addSql('ALTER TABLE sit_family_group ADD CONSTRAINT FK_D902D1F14AE25278 FOREIGN KEY (support_group_id) REFERENCES support_group (id)');
        $this->addSql('ALTER TABLE sit_family_person ADD CONSTRAINT FK_15DCE75697A920F1 FOREIGN KEY (support_person_id) REFERENCES support_person (id)');
        $this->addSql('DROP TABLE sit_budget_grp');
        $this->addSql('DROP TABLE sit_family_grp');
        $this->addSql('DROP TABLE sit_family_pers');
        $this->addSql('DROP TABLE support_grp');
        $this->addSql('DROP TABLE support_pers');
        $this->addSql('DROP INDEX UNIQ_8BAC00F0F79039BF ON sit_adm');
        $this->addSql('ALTER TABLE sit_adm CHANGE support_pers_id support_person_id INT NOT NULL');
        $this->addSql('ALTER TABLE sit_adm ADD CONSTRAINT FK_8BAC00F097A920F1 FOREIGN KEY (support_person_id) REFERENCES support_person (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8BAC00F097A920F1 ON sit_adm (support_person_id)');
        $this->addSql('DROP INDEX UNIQ_318331FEF79039BF ON sit_budget');
        $this->addSql('ALTER TABLE sit_budget CHANGE support_pers_id support_person_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sit_budget ADD CONSTRAINT FK_318331FE97A920F1 FOREIGN KEY (support_person_id) REFERENCES support_person (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_318331FE97A920F1 ON sit_budget (support_person_id)');
        $this->addSql('DROP INDEX UNIQ_661144AA5DE471A0 ON sit_housing');
        $this->addSql('ALTER TABLE sit_housing CHANGE support_grp_id support_group_id INT NOT NULL');
        $this->addSql('ALTER TABLE sit_housing ADD CONSTRAINT FK_661144AA4AE25278 FOREIGN KEY (support_group_id) REFERENCES support_group (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_661144AA4AE25278 ON sit_housing (support_group_id)');
        $this->addSql('DROP INDEX UNIQ_9DDF846F79039BF ON sit_prof');
        $this->addSql('ALTER TABLE sit_prof CHANGE support_pers_id support_person_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sit_prof ADD CONSTRAINT FK_9DDF84697A920F1 FOREIGN KEY (support_person_id) REFERENCES support_person (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9DDF84697A920F1 ON sit_prof (support_person_id)');
        $this->addSql('DROP INDEX UNIQ_331027025DE471A0 ON sit_social');
        $this->addSql('ALTER TABLE sit_social CHANGE support_grp_id support_group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sit_social ADD CONSTRAINT FK_331027024AE25278 FOREIGN KEY (support_group_id) REFERENCES support_group (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_331027024AE25278 ON sit_social (support_group_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE support_person DROP FOREIGN KEY FK_BC2631864AE25278');
        $this->addSql('ALTER TABLE sit_budget_group DROP FOREIGN KEY FK_8E64D7744AE25278');
        $this->addSql('ALTER TABLE sit_family_group DROP FOREIGN KEY FK_D902D1F14AE25278');
        $this->addSql('ALTER TABLE sit_housing DROP FOREIGN KEY FK_661144AA4AE25278');
        $this->addSql('ALTER TABLE sit_social DROP FOREIGN KEY FK_331027024AE25278');
        $this->addSql('ALTER TABLE sit_adm DROP FOREIGN KEY FK_8BAC00F097A920F1');
        $this->addSql('ALTER TABLE sit_budget DROP FOREIGN KEY FK_318331FE97A920F1');
        $this->addSql('ALTER TABLE sit_family_person DROP FOREIGN KEY FK_15DCE75697A920F1');
        $this->addSql('ALTER TABLE sit_prof DROP FOREIGN KEY FK_9DDF84697A920F1');
        $this->addSql('CREATE TABLE sit_budget_grp (id INT AUTO_INCREMENT NOT NULL, support_grp_id INT NOT NULL, ressources_grp_amt INT DEFAULT NULL, charges_grp_amt INT DEFAULT NULL, debts_grp_amt INT DEFAULT NULL, tax_income_n1amt INT DEFAULT NULL, tax_income_n2amt INT DEFAULT NULL, comment_sit_budget LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, monthly_repayment_amt DOUBLE PRECISION DEFAULT NULL, budget_balance_amt INT DEFAULT NULL, UNIQUE INDEX UNIQ_B0DE52845DE471A0 (support_grp_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE sit_family_grp (id INT AUTO_INCREMENT NOT NULL, support_grp_id INT DEFAULT NULL, nb_dependent_children SMALLINT DEFAULT NULL, unborn_child SMALLINT DEFAULT NULL, exp_date_childbirth DATE DEFAULT NULL, pregnancy_type SMALLINT DEFAULT NULL, faml_reunification SMALLINT DEFAULT NULL, nb_people_reunification SMALLINT DEFAULT NULL, caf_id VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, comment_sit_family_grp LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, UNIQUE INDEX UNIQ_D8F6148C5DE471A0 (support_grp_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE sit_family_pers (id INT AUTO_INCREMENT NOT NULL, support_pers_id INT NOT NULL, marital_status SMALLINT DEFAULT NULL, childcare_school SMALLINT DEFAULT NULL, childcare_school_location VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, child_to_host SMALLINT DEFAULT NULL, child_dependance SMALLINT DEFAULT NULL, UNIQUE INDEX UNIQ_19494E45F79039BF (support_pers_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE support_grp (id INT AUTO_INCREMENT NOT NULL, group_people_id INT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, service_id INT DEFAULT NULL, referent_id INT DEFAULT NULL, referent2_id INT DEFAULT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, status INT NOT NULL, comment LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_E468E757ED5CA9E6 (service_id), INDEX IDX_E468E757925FA220 (referent2_id), INDEX IDX_E468E757B03A8386 (created_by_id), INDEX IDX_E468E757896DBBDE (updated_by_id), INDEX IDX_E468E75735E47E35 (referent_id), INDEX IDX_E468E757F1F495D7 (group_people_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE support_pers (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, support_grp_id INT NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, status INT NOT NULL, comment LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_874DBEA5DE471A0 (support_grp_id), INDEX IDX_874DBEA217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE sit_budget_grp ADD CONSTRAINT FK_B0DE52845DE471A0 FOREIGN KEY (support_grp_id) REFERENCES support_grp (id)');
        $this->addSql('ALTER TABLE sit_family_grp ADD CONSTRAINT FK_D8F6148C5DE471A0 FOREIGN KEY (support_grp_id) REFERENCES support_grp (id)');
        $this->addSql('ALTER TABLE sit_family_pers ADD CONSTRAINT FK_19494E45F79039BF FOREIGN KEY (support_pers_id) REFERENCES support_pers (id)');
        $this->addSql('ALTER TABLE support_grp ADD CONSTRAINT FK_E468E75735E47E35 FOREIGN KEY (referent_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE support_grp ADD CONSTRAINT FK_E468E757896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE support_grp ADD CONSTRAINT FK_E468E757925FA220 FOREIGN KEY (referent2_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE support_grp ADD CONSTRAINT FK_E468E757B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE support_grp ADD CONSTRAINT FK_E468E757ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('ALTER TABLE support_grp ADD CONSTRAINT FK_E468E757F1F495D7 FOREIGN KEY (group_people_id) REFERENCES group_people (id)');
        $this->addSql('ALTER TABLE support_pers ADD CONSTRAINT FK_874DBEA217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE support_pers ADD CONSTRAINT FK_874DBEA5DE471A0 FOREIGN KEY (support_grp_id) REFERENCES support_grp (id)');
        $this->addSql('DROP TABLE support_group');
        $this->addSql('DROP TABLE support_person');
        $this->addSql('DROP TABLE sit_budget_group');
        $this->addSql('DROP TABLE sit_family_group');
        $this->addSql('DROP TABLE sit_family_person');
        $this->addSql('DROP INDEX UNIQ_8BAC00F097A920F1 ON sit_adm');
        $this->addSql('ALTER TABLE sit_adm CHANGE support_person_id support_pers_id INT NOT NULL');
        $this->addSql('ALTER TABLE sit_adm ADD CONSTRAINT FK_8BAC00F0F79039BF FOREIGN KEY (support_pers_id) REFERENCES support_pers (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8BAC00F0F79039BF ON sit_adm (support_pers_id)');
        $this->addSql('DROP INDEX UNIQ_318331FE97A920F1 ON sit_budget');
        $this->addSql('ALTER TABLE sit_budget CHANGE support_person_id support_pers_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sit_budget ADD CONSTRAINT FK_318331FEF79039BF FOREIGN KEY (support_pers_id) REFERENCES support_pers (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_318331FEF79039BF ON sit_budget (support_pers_id)');
        $this->addSql('DROP INDEX UNIQ_661144AA4AE25278 ON sit_housing');
        $this->addSql('ALTER TABLE sit_housing CHANGE support_group_id support_grp_id INT NOT NULL');
        $this->addSql('ALTER TABLE sit_housing ADD CONSTRAINT FK_661144AA5DE471A0 FOREIGN KEY (support_grp_id) REFERENCES support_grp (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_661144AA5DE471A0 ON sit_housing (support_grp_id)');
        $this->addSql('DROP INDEX UNIQ_9DDF84697A920F1 ON sit_prof');
        $this->addSql('ALTER TABLE sit_prof CHANGE support_person_id support_pers_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sit_prof ADD CONSTRAINT FK_9DDF846F79039BF FOREIGN KEY (support_pers_id) REFERENCES support_pers (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9DDF846F79039BF ON sit_prof (support_pers_id)');
        $this->addSql('DROP INDEX UNIQ_331027024AE25278 ON sit_social');
        $this->addSql('ALTER TABLE sit_social CHANGE support_group_id support_grp_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE sit_social ADD CONSTRAINT FK_331027025DE471A0 FOREIGN KEY (support_grp_id) REFERENCES support_grp (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_331027025DE471A0 ON sit_social (support_grp_id)');
    }
}
