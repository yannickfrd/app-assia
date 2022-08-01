<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220729135309 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename fields to have the same names in entities and database';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE eval_family_person 
            CHANGE childcare_or_school school_or_childcare SMALLINT DEFAULT NULL, 
            CHANGE childcare_school school_childcare_type SMALLINT DEFAULT NULL, 
            CHANGE childcare_school_location school_comment VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('ALTER TABLE eval_housing_group 
            CHANGE dalo_commission dalo_action SMALLINT DEFAULT NULL, 
            CHANGE dalo_requalified_daho dalo_type SMALLINT DEFAULT NULL, 
            CHANGE domiciliation_dept domiciliation_zipcode VARCHAR(10) DEFAULT NULL
        ');
        $this->addSql('ALTER TABLE eval_social_person 
            CHANGE care_support home_care_support SMALLINT DEFAULT NULL, 
            CHANGE care_support_type home_care_support_type SMALLINT DEFAULT NULL
        ');
        $this->addSql('ALTER TABLE indicator 
            CHANGE nb_created_contributions nb_created_payments INT DEFAULT NULL
        ');
        $this->addSql('ALTER TABLE place 
            CHANGE opening_date start_date DATE DEFAULT NULL, 
            CHANGE closing_date end_date DATE DEFAULT NULL, 
            CHANGE places_number nb_places INT DEFAULT NULL
        ');
        $this->addSql('ALTER TABLE referent 
            CHANGE email1 email VARCHAR(100) DEFAULT NULL
        ');
        $this->addSql('ALTER TABLE service 
            CHANGE opening_date start_date DATE DEFAULT NULL, 
            CHANGE closing_date end_date DATE DEFAULT NULL
        ');
        $this->addSql('ALTER TABLE user 
            CHANGE phone phone1 VARCHAR(20) DEFAULT NULL
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE eval_family_person 
            CHANGE school_or_childcare childcare_or_school SMALLINT DEFAULT NULL, 
            CHANGE childcare_school_type childcare_school SMALLINT DEFAULT NULL, 
            CHANGE school_comment childcare_school_location VARCHAR(255) DEFAULT NULL
        ');
        $this->addSql('ALTER TABLE eval_housing_group 
            CHANGE dalo_action dalo_commission SMALLINT DEFAULT NULL, 
            CHANGE dalo_type dalo_requalified_daho SMALLINT DEFAULT NULL, 
            CHANGE domiciliation_zipcode domiciliation_dept VARCHAR(10) DEFAULT NULL
        ');
        $this->addSql('ALTER TABLE eval_social_person 
            CHANGE home_care_support care_support SMALLINT DEFAULT NULL, 
            CHANGE home_care_support_type care_support_type SMALLINT DEFAULT NULL
        ');
        $this->addSql('ALTER TABLE indicator 
            CHANGE nb_created_payments nb_created_contributions INT DEFAULT NULL
        ');
        $this->addSql('ALTER TABLE place 
            CHANGE start_date opening_date DATE DEFAULT NULL, 
            CHANGE end_date closing_date DATE DEFAULT NULL, 
            CHANGE nb_places places_number INT DEFAULT NULL
        ');
        $this->addSql('ALTER TABLE referent 
            CHANGE email email1 VARCHAR(100) DEFAULT NULL
        ');
        $this->addSql('ALTER TABLE service 
            CHANGE start_date opening_date DATE DEFAULT NULL, 
            CHANGE end_date closing_date DATE DEFAULT NULL
        ');
        $this->addSql('ALTER TABLE user 
            CHANGE phone1 phone VARCHAR(20) DEFAULT NULL
        ');
    }
}
