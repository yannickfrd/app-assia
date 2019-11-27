<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191125165933 extends AbstractMigration
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
        $this->addSql('CREATE TABLE person (id INT AUTO_INCREMENT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, lastname VARCHAR(50) NOT NULL, firstname VARCHAR(50) NOT NULL, maiden_name VARCHAR(100) DEFAULT NULL, usename VARCHAR(50) DEFAULT NULL, birthdate DATETIME DEFAULT NULL, gender SMALLINT NOT NULL, phone1 VARCHAR(20) DEFAULT NULL, phone2 VARCHAR(20) DEFAULT NULL, email VARCHAR(100) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_34DCD176B03A8386 (created_by_id), INDEX IDX_34DCD176896DBBDE (updated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pole (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(100) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, zip_code VARCHAR(10) DEFAULT NULL, director VARCHAR(255) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, color VARCHAR(50) DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role_person (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, group_people_id INT DEFAULT NULL, head TINYINT(1) DEFAULT \'0\' NOT NULL, role SMALLINT NOT NULL, created_at DATETIME DEFAULT NULL, created_by INT DEFAULT NULL, INDEX IDX_9FFA30C7217BBB47 (person_id), INDEX IDX_9FFA30C7F1F495D7 (group_people_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role_user (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, service_id INT DEFAULT NULL, role INT NOT NULL, INDEX IDX_332CA4DDA76ED395 (user_id), INDEX IDX_332CA4DDED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service (id INT AUTO_INCREMENT NOT NULL, pole_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(100) DEFAULT NULL, phone VARCHAR(20) DEFAULT NULL, address VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, zip_code VARCHAR(10) DEFAULT NULL, chief VARCHAR(255) DEFAULT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_E19D9AD2419C3385 (pole_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sit_adm (id INT AUTO_INCREMENT NOT NULL, support_pers_id INT NOT NULL, nationality SMALLINT DEFAULT NULL, country VARCHAR(100) DEFAULT NULL, paper SMALLINT DEFAULT NULL, paper_type SMALLINT DEFAULT NULL, right_reside SMALLINT DEFAULT NULL, appl_resid_permit SMALLINT DEFAULT NULL, end_date_valid_permit DATE DEFAULT NULL, renewal_date_permit DATE DEFAULT NULL, nb_renewals SMALLINT DEFAULT NULL, no_rights_open TINYINT(1) DEFAULT NULL, right_work TINYINT(1) DEFAULT NULL, right_social_benf TINYINT(1) DEFAULT NULL, housing_alw TINYINT(1) DEFAULT NULL, right_social_secu SMALLINT DEFAULT NULL, social_secu SMALLINT DEFAULT NULL, social_secu_office VARCHAR(255) DEFAULT NULL, comment_sit_adm LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_8BAC00F0F79039BF (support_pers_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sit_budget (id INT AUTO_INCREMENT NOT NULL, support_pers_id INT NOT NULL, ressources SMALLINT DEFAULT NULL, ressources_amt DOUBLE PRECISION DEFAULT NULL, dis_adult_alw TINYINT(1) DEFAULT NULL, dis_child_alw TINYINT(1) DEFAULT NULL, unempl_benf TINYINT(1) DEFAULT NULL, asylum_seeker_alw TINYINT(1) DEFAULT NULL, temp_waiting_alw TINYINT(1) DEFAULT NULL, family_alw TINYINT(1) DEFAULT NULL, solidarity_alw TINYINT(1) DEFAULT NULL, paid_training TINYINT(1) DEFAULT NULL, youth_guarantee TINYINT(1) DEFAULT NULL, maintenance TINYINT(1) DEFAULT NULL, activity_bonus TINYINT(1) DEFAULT NULL, pension_benf TINYINT(1) DEFAULT NULL, min_income TINYINT(1) DEFAULT NULL, salary TINYINT(1) DEFAULT NULL, other_income TINYINT(1) DEFAULT NULL, other_income_precision VARCHAR(255) DEFAULT NULL, dis_adult_alw_amt DOUBLE PRECISION DEFAULT NULL, dis_child_alw_amt DOUBLE PRECISION DEFAULT NULL, unempl_benf_amt DOUBLE PRECISION DEFAULT NULL, asylum_seeker_alw_amt DOUBLE PRECISION DEFAULT NULL, temp_waiting_alw_amt DOUBLE PRECISION DEFAULT NULL, family_alw_amt DOUBLE PRECISION DEFAULT NULL, solidarity_alw_amt DOUBLE PRECISION DEFAULT NULL, paid_training_amt DOUBLE PRECISION DEFAULT NULL, youth_guarantee_amt DOUBLE PRECISION DEFAULT NULL, maintenance_amt DOUBLE PRECISION DEFAULT NULL, activity_bonus_amt DOUBLE PRECISION DEFAULT NULL, pension_benf_amt DOUBLE PRECISION DEFAULT NULL, min_income_amt DOUBLE PRECISION DEFAULT NULL, salary_amt DOUBLE PRECISION DEFAULT NULL, other_income_amt DOUBLE PRECISION DEFAULT NULL, tax_income_n1 DOUBLE PRECISION DEFAULT NULL, tax_income_n2 DOUBLE PRECISION DEFAULT NULL, ressources_comment LONGTEXT DEFAULT NULL, charges SMALLINT DEFAULT NULL, charges_amt DOUBLE PRECISION DEFAULT NULL, rent TINYINT(1) DEFAULT NULL, electricity_gas TINYINT(1) DEFAULT NULL, water TINYINT(1) DEFAULT NULL, insurance TINYINT(1) DEFAULT NULL, mutual TINYINT(1) DEFAULT NULL, taxes TINYINT(1) DEFAULT NULL, transport TINYINT(1) DEFAULT NULL, childcare TINYINT(1) DEFAULT NULL, alimony TINYINT(1) DEFAULT NULL, phone TINYINT(1) DEFAULT NULL, other_charge TINYINT(1) DEFAULT NULL, other_charge_precision VARCHAR(255) DEFAULT NULL, rent_amt DOUBLE PRECISION DEFAULT NULL, electricity_gas_amt DOUBLE PRECISION DEFAULT NULL, water_amt DOUBLE PRECISION DEFAULT NULL, insurance_amt DOUBLE PRECISION DEFAULT NULL, mutual_amt DOUBLE PRECISION DEFAULT NULL, taxes_amt DOUBLE PRECISION DEFAULT NULL, transport_amt DOUBLE PRECISION DEFAULT NULL, childcare_amt DOUBLE PRECISION DEFAULT NULL, alimony_amt DOUBLE PRECISION DEFAULT NULL, phone_amt DOUBLE PRECISION DEFAULT NULL, other_charge_amt DOUBLE PRECISION DEFAULT NULL, charge_comment LONGTEXT DEFAULT NULL, debts SMALLINT DEFAULT NULL, debt_rental TINYINT(1) DEFAULT NULL, debt_consr_credit TINYINT(1) DEFAULT NULL, debt_mortgage TINYINT(1) DEFAULT NULL, debt_fines TINYINT(1) DEFAULT NULL, debt_tax_delays TINYINT(1) DEFAULT NULL, debt_bank_overdrafts TINYINT(1) DEFAULT NULL, debt_other TINYINT(1) DEFAULT NULL, debt_other_precision VARCHAR(255) DEFAULT NULL, debt_amt DOUBLE PRECISION DEFAULT NULL, debt_comment LONGTEXT DEFAULT NULL, monthly_repayment_amt DOUBLE PRECISION DEFAULT NULL, over_indebt_record SMALLINT DEFAULT NULL, over_indebt_record_date DATE DEFAULT NULL, settlementplan SMALLINT DEFAULT NULL, moratorium SMALLINT DEFAULT NULL, comment_sit_budget LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_318331FEF79039BF (support_pers_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sit_family_grp (id INT AUTO_INCREMENT NOT NULL, support_grp_id INT DEFAULT NULL, nb_dependent_children SMALLINT DEFAULT NULL, unborn_child SMALLINT DEFAULT NULL, exp_date_childbirth DATE DEFAULT NULL, pregnancy_type SMALLINT DEFAULT NULL, faml_reunification SMALLINT DEFAULT NULL, nb_people_reunification SMALLINT DEFAULT NULL, caf_id VARCHAR(255) DEFAULT NULL, comment_sit_family_grp LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_D8F6148C5DE471A0 (support_grp_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sit_family_pers (id INT AUTO_INCREMENT NOT NULL, support_pers_id INT DEFAULT NULL, marital_status SMALLINT DEFAULT NULL, childcare_school SMALLINT DEFAULT NULL, childcare_school_location VARCHAR(255) DEFAULT NULL, child_to_host SMALLINT DEFAULT NULL, child_dependance SMALLINT DEFAULT NULL, UNIQUE INDEX UNIQ_19494E45F79039BF (support_pers_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sit_housing (id INT AUTO_INCREMENT NOT NULL, support_grp_id INT NOT NULL, dls SMALLINT DEFAULT NULL, dls_id VARCHAR(255) DEFAULT NULL, dls_date DATE DEFAULT NULL, dls_renewal_date DATE DEFAULT NULL, housing_wishes VARCHAR(255) DEFAULT NULL, cities_wishes VARCHAR(255) DEFAULT NULL, specificities VARCHAR(255) DEFAULT NULL, syplo_id VARCHAR(255) DEFAULT NULL, syplo_date DATE DEFAULT NULL, dalo_commission SMALLINT DEFAULT NULL, dalo_record_date DATE DEFAULT NULL, requalified_dalo SMALLINT DEFAULT NULL, decision_date DATE DEFAULT NULL, hsg_action_eligibility SMALLINT DEFAULT NULL, hsg_action_record SMALLINT DEFAULT NULL, hsg_action_date DATE DEFAULT NULL, hsg_action_dept VARCHAR(10) DEFAULT NULL, hsg_action_record_id VARCHAR(255) DEFAULT NULL, expulsion_in_progress SMALLINT DEFAULT NULL, public_force SMALLINT DEFAULT NULL, public_force_date DATE DEFAULT NULL, expulsion_comment LONGTEXT DEFAULT NULL, housing_experience SMALLINT DEFAULT NULL, housing_expe_comment LONGTEXT DEFAULT NULL, fsl TINYINT(1) DEFAULT NULL, fsl_eligibility TINYINT(1) DEFAULT NULL, caf_eligibility TINYINT(1) DEFAULT NULL, other_helps TINYINT(1) DEFAULT NULL, heps_precision VARCHAR(255) DEFAULT NULL, comment_sit_housing LONGTEXT DEFAULT NULL, housing_status SMALLINT DEFAULT NULL, housing SMALLINT DEFAULT NULL, housing_address VARCHAR(255) DEFAULT NULL, housing_city VARCHAR(100) DEFAULT NULL, housing_dept VARCHAR(10) DEFAULT NULL, domiciliation SMALLINT DEFAULT NULL, domiciliation_address VARCHAR(255) DEFAULT NULL, domiciliation_city VARCHAR(100) DEFAULT NULL, domiciliation_dept VARCHAR(10) DEFAULT NULL, syplo SMALLINT DEFAULT NULL, UNIQUE INDEX UNIQ_661144AA5DE471A0 (support_grp_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sit_prof (id INT AUTO_INCREMENT NOT NULL, support_pers_id INT DEFAULT NULL, prof_status SMALLINT DEFAULT NULL, school_level SMALLINT DEFAULT NULL, job_type VARCHAR(255) DEFAULT NULL, contract_type SMALLINT DEFAULT NULL, contract_start_date DATE DEFAULT NULL, contract_end_date DATE DEFAULT NULL, nb_working_hours VARCHAR(50) DEFAULT NULL, working_hours VARCHAR(100) DEFAULT NULL, work_place VARCHAR(100) DEFAULT NULL, employer_name VARCHAR(100) DEFAULT NULL, transport_means VARCHAR(255) DEFAULT NULL, rqth SMALLINT DEFAULT NULL, comment_sit_prof LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_9DDF846F79039BF (support_pers_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sit_social (id INT AUTO_INCREMENT NOT NULL, support_grp_id INT DEFAULT NULL, reason_request SMALLINT DEFAULT NULL, wandering_time SMALLINT DEFAULT NULL, spe_animal TINYINT(1) DEFAULT NULL, spe_animal_name VARCHAR(255) DEFAULT NULL, spe_wheelchair TINYINT(1) DEFAULT NULL, spe_reduced_mobility TINYINT(1) DEFAULT NULL, spe_violence_victim TINYINT(1) DEFAULT NULL, spe_dom_violence_victim TINYINT(1) DEFAULT NULL, spe_ase TINYINT(1) DEFAULT NULL, spe_other TINYINT(1) DEFAULT NULL, spe_other_precision VARCHAR(255) DEFAULT NULL, spe_comment LONGTEXT DEFAULT NULL, comment_sit_social LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_331027025DE471A0 (support_grp_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE support_grp (id INT AUTO_INCREMENT NOT NULL, group_people_id INT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, service_id INT DEFAULT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, status INT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_E468E757F1F495D7 (group_people_id), INDEX IDX_E468E757B03A8386 (created_by_id), INDEX IDX_E468E757896DBBDE (updated_by_id), INDEX IDX_E468E757ED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE support_pers (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, support_grp_id INT NOT NULL, sit_budget_id INT DEFAULT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, status INT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_874DBEA217BBB47 (person_id), INDEX IDX_874DBEA5DE471A0 (support_grp_id), UNIQUE INDEX UNIQ_874DBEA6BF74EF3 (sit_budget_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
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
        $this->addSql('ALTER TABLE sit_adm ADD CONSTRAINT FK_8BAC00F0F79039BF FOREIGN KEY (support_pers_id) REFERENCES support_pers (id)');
        $this->addSql('ALTER TABLE sit_budget ADD CONSTRAINT FK_318331FEF79039BF FOREIGN KEY (support_pers_id) REFERENCES support_pers (id)');
        $this->addSql('ALTER TABLE sit_family_grp ADD CONSTRAINT FK_D8F6148C5DE471A0 FOREIGN KEY (support_grp_id) REFERENCES support_grp (id)');
        $this->addSql('ALTER TABLE sit_family_pers ADD CONSTRAINT FK_19494E45F79039BF FOREIGN KEY (support_pers_id) REFERENCES support_pers (id)');
        $this->addSql('ALTER TABLE sit_housing ADD CONSTRAINT FK_661144AA5DE471A0 FOREIGN KEY (support_grp_id) REFERENCES support_grp (id)');
        $this->addSql('ALTER TABLE sit_prof ADD CONSTRAINT FK_9DDF846F79039BF FOREIGN KEY (support_pers_id) REFERENCES support_pers (id)');
        $this->addSql('ALTER TABLE sit_social ADD CONSTRAINT FK_331027025DE471A0 FOREIGN KEY (support_grp_id) REFERENCES support_grp (id)');
        $this->addSql('ALTER TABLE support_grp ADD CONSTRAINT FK_E468E757F1F495D7 FOREIGN KEY (group_people_id) REFERENCES group_people (id)');
        $this->addSql('ALTER TABLE support_grp ADD CONSTRAINT FK_E468E757B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE support_grp ADD CONSTRAINT FK_E468E757896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE support_grp ADD CONSTRAINT FK_E468E757ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('ALTER TABLE support_pers ADD CONSTRAINT FK_874DBEA217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE support_pers ADD CONSTRAINT FK_874DBEA5DE471A0 FOREIGN KEY (support_grp_id) REFERENCES support_grp (id)');
        $this->addSql('ALTER TABLE support_pers ADD CONSTRAINT FK_874DBEA6BF74EF3 FOREIGN KEY (sit_budget_id) REFERENCES sit_budget (id)');
        $this->addSql('ALTER TABLE user_connection ADD CONSTRAINT FK_8E90B58AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE role_person DROP FOREIGN KEY FK_9FFA30C7F1F495D7');
        $this->addSql('ALTER TABLE support_grp DROP FOREIGN KEY FK_E468E757F1F495D7');
        $this->addSql('ALTER TABLE role_person DROP FOREIGN KEY FK_9FFA30C7217BBB47');
        $this->addSql('ALTER TABLE support_pers DROP FOREIGN KEY FK_874DBEA217BBB47');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD2419C3385');
        $this->addSql('ALTER TABLE role_user DROP FOREIGN KEY FK_332CA4DDED5CA9E6');
        $this->addSql('ALTER TABLE support_grp DROP FOREIGN KEY FK_E468E757ED5CA9E6');
        $this->addSql('ALTER TABLE support_pers DROP FOREIGN KEY FK_874DBEA6BF74EF3');
        $this->addSql('ALTER TABLE sit_family_grp DROP FOREIGN KEY FK_D8F6148C5DE471A0');
        $this->addSql('ALTER TABLE sit_housing DROP FOREIGN KEY FK_661144AA5DE471A0');
        $this->addSql('ALTER TABLE sit_social DROP FOREIGN KEY FK_331027025DE471A0');
        $this->addSql('ALTER TABLE support_pers DROP FOREIGN KEY FK_874DBEA5DE471A0');
        $this->addSql('ALTER TABLE sit_adm DROP FOREIGN KEY FK_8BAC00F0F79039BF');
        $this->addSql('ALTER TABLE sit_budget DROP FOREIGN KEY FK_318331FEF79039BF');
        $this->addSql('ALTER TABLE sit_family_pers DROP FOREIGN KEY FK_19494E45F79039BF');
        $this->addSql('ALTER TABLE sit_prof DROP FOREIGN KEY FK_9DDF846F79039BF');
        $this->addSql('ALTER TABLE group_people DROP FOREIGN KEY FK_FB90B2F6B03A8386');
        $this->addSql('ALTER TABLE group_people DROP FOREIGN KEY FK_FB90B2F6896DBBDE');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176B03A8386');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176896DBBDE');
        $this->addSql('ALTER TABLE role_user DROP FOREIGN KEY FK_332CA4DDA76ED395');
        $this->addSql('ALTER TABLE support_grp DROP FOREIGN KEY FK_E468E757B03A8386');
        $this->addSql('ALTER TABLE support_grp DROP FOREIGN KEY FK_E468E757896DBBDE');
        $this->addSql('ALTER TABLE user_connection DROP FOREIGN KEY FK_8E90B58AA76ED395');
        $this->addSql('DROP TABLE group_people');
        $this->addSql('DROP TABLE person');
        $this->addSql('DROP TABLE pole');
        $this->addSql('DROP TABLE role_person');
        $this->addSql('DROP TABLE role_user');
        $this->addSql('DROP TABLE service');
        $this->addSql('DROP TABLE sit_adm');
        $this->addSql('DROP TABLE sit_budget');
        $this->addSql('DROP TABLE sit_family_grp');
        $this->addSql('DROP TABLE sit_family_pers');
        $this->addSql('DROP TABLE sit_housing');
        $this->addSql('DROP TABLE sit_prof');
        $this->addSql('DROP TABLE sit_social');
        $this->addSql('DROP TABLE support_grp');
        $this->addSql('DROP TABLE support_pers');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_connection');
    }
}
