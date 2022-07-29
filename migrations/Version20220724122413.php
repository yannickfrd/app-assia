<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20220724122413 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix relations one-to-one for tables `support_group`, `support_person`, `evaluation_group` and `evaluation_person`';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE support_group ADD eval_init_group_id INT DEFAULT NULL, ADD origin_request_id INT DEFAULT NULL, ADD avdl_id INT DEFAULT NULL, ADD hotel_support_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE support_group ADD CONSTRAINT FK_9F7A521DEC74FA8D FOREIGN KEY (eval_init_group_id) REFERENCES eval_init_group (id)');
        $this->addSql('ALTER TABLE support_group ADD CONSTRAINT FK_9F7A521DFBA5E2E8 FOREIGN KEY (origin_request_id) REFERENCES origin_request (id)');
        $this->addSql('ALTER TABLE support_group ADD CONSTRAINT FK_9F7A521DE994F3E7 FOREIGN KEY (avdl_id) REFERENCES avdl (id)');
        $this->addSql('ALTER TABLE support_group ADD CONSTRAINT FK_9F7A521D76C15EE8 FOREIGN KEY (hotel_support_id) REFERENCES hotel_support (id)');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_9F7A521DEC74FA8D ON support_group (eval_init_group_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9F7A521DFBA5E2E8 ON support_group (origin_request_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9F7A521DE994F3E7 ON support_group (avdl_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9F7A521D76C15EE8 ON support_group (hotel_support_id)');

        $this->addSql('ALTER TABLE support_person ADD eval_init_person_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE support_person ADD CONSTRAINT FK_BC2631865AD8B0CA FOREIGN KEY (eval_init_person_id) REFERENCES eval_init_person (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC2631865AD8B0CA ON support_person (eval_init_person_id)');

        $this->addSql('UPDATE support_group LEFT JOIN origin_request  
            ON support_group.id = origin_request.support_group_id 
            SET support_group.origin_request_id = origin_request.id
        ');
        $this->addSql('UPDATE support_group LEFT JOIN avdl  
            ON support_group.id = avdl.support_group_id 
            SET support_group.avdl_id = avdl.id
        ');
        $this->addSql('UPDATE support_group LEFT JOIN hotel_support  
            ON support_group.id = hotel_support.support_group_id 
            SET support_group.hotel_support_id = hotel_support.id
        ');

        $this->addSql('UPDATE support_group LEFT JOIN evaluation_group
            ON support_group.id = evaluation_group.support_group_id
            SET support_group.eval_init_group_id = evaluation_group.eval_init_group_id
            WHERE support_group.eval_init_group_id IS NULL AND evaluation_group.eval_init_group_id IS NOT NULL
        ');

        $this->addSql('UPDATE support_group LEFT JOIN eval_init_group  
            ON support_group.id = eval_init_group.support_group_id 
            SET support_group.eval_init_group_id = eval_init_group.id
            WHERE support_group.eval_init_group_id IS NULL
        ');

        $this->addSql('UPDATE support_person LEFT JOIN evaluation_person
            ON support_person.id = evaluation_person.support_person_id
            SET support_person.eval_init_person_id = evaluation_person.eval_init_person_id
            WHERE support_person.eval_init_person_id IS NULL AND evaluation_person.eval_init_person_id IS NOT NULL
        ');

        // $this->addSql('UPDATE support_person LEFT JOIN eval_init_person
        //     ON support_person.id = eval_init_person.support_person_id
        //     SET support_person.eval_init_person_id = eval_init_person.id
        //     WHERE support_person.eval_init_person_id IS NULL
        // ');

        $this->addSql('ALTER TABLE avdl DROP FOREIGN KEY FK_B737D8484AE25278');
        $this->addSql('DROP INDEX UNIQ_B737D8484AE25278 ON avdl');
        $this->addSql('ALTER TABLE avdl DROP support_group_id');

        $this->addSql('ALTER TABLE eval_init_group DROP FOREIGN KEY FK_53AF32384AE25278');
        $this->addSql('DROP INDEX UNIQ_995638124AE25278 ON eval_init_group');
        $this->addSql('ALTER TABLE eval_init_group DROP support_group_id');

        $this->addSql('ALTER TABLE hotel_support DROP FOREIGN KEY FK_534EF58C4AE25278');
        $this->addSql('DROP INDEX UNIQ_534EF58C4AE25278 ON hotel_support');
        $this->addSql('ALTER TABLE hotel_support DROP support_group_id');

        $this->addSql('ALTER TABLE origin_request DROP FOREIGN KEY FK_8EE09AF24AE25278');
        $this->addSql('DROP INDEX UNIQ_8EE09AF24AE25278 ON origin_request');
        $this->addSql('ALTER TABLE origin_request DROP support_group_id');

        $this->addSql('ALTER TABLE eval_init_person DROP FOREIGN KEY FK_F7EE30A197A920F1');
        $this->addSql('DROP INDEX UNIQ_2C9F007D97A920F1 ON eval_init_person');
        $this->addSql('ALTER TABLE eval_init_person DROP support_person_id');

        $this->addSql('ALTER TABLE evaluation_group ADD eval_social_group_id INT DEFAULT NULL, ADD eval_family_group_id INT DEFAULT NULL, ADD eval_housing_group_id INT DEFAULT NULL, ADD eval_budget_group_id INT DEFAULT NULL, ADD eval_hotel_life_group_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE evaluation_group ADD CONSTRAINT FK_A8373934E128B196 FOREIGN KEY (eval_social_group_id) REFERENCES eval_social_group (id)');
        $this->addSql('ALTER TABLE evaluation_group ADD CONSTRAINT FK_A8373934ADA3CEC9 FOREIGN KEY (eval_family_group_id) REFERENCES eval_family_group (id)');
        $this->addSql('ALTER TABLE evaluation_group ADD CONSTRAINT FK_A8373934A8CFADFB FOREIGN KEY (eval_housing_group_id) REFERENCES eval_housing_group (id)');
        $this->addSql('ALTER TABLE evaluation_group ADD CONSTRAINT FK_A8373934B8D6741E FOREIGN KEY (eval_budget_group_id) REFERENCES eval_budget_group (id)');
        $this->addSql('ALTER TABLE evaluation_group ADD CONSTRAINT FK_A837393419ECF001 FOREIGN KEY (eval_hotel_life_group_id) REFERENCES eval_hotel_life_group (id)');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_A8373934E128B196 ON evaluation_group (eval_social_group_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A8373934ADA3CEC9 ON evaluation_group (eval_family_group_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A8373934A8CFADFB ON evaluation_group (eval_housing_group_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A8373934B8D6741E ON evaluation_group (eval_budget_group_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A837393419ECF001 ON evaluation_group (eval_hotel_life_group_id)');

        $this->addSql('ALTER TABLE evaluation_person ADD eval_adm_person_id INT DEFAULT NULL, ADD eval_budget_person_id INT DEFAULT NULL, ADD eval_family_person_id INT DEFAULT NULL, ADD eval_prof_person_id INT DEFAULT NULL, ADD eval_social_person_id INT DEFAULT NULL, ADD eval_justice_person_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE evaluation_person ADD CONSTRAINT FK_FEA3E481AACB6D44 FOREIGN KEY (eval_adm_person_id) REFERENCES eval_adm_person (id)');
        $this->addSql('ALTER TABLE evaluation_person ADD CONSTRAINT FK_FEA3E481338AD0BA FOREIGN KEY (eval_budget_person_id) REFERENCES eval_budget_person (id)');
        $this->addSql('ALTER TABLE evaluation_person ADD CONSTRAINT FK_FEA3E4812B28E277 FOREIGN KEY (eval_family_person_id) REFERENCES eval_family_person (id)');
        $this->addSql('ALTER TABLE evaluation_person ADD CONSTRAINT FK_FEA3E481A526B42D FOREIGN KEY (eval_prof_person_id) REFERENCES eval_prof_person (id)');
        $this->addSql('ALTER TABLE evaluation_person ADD CONSTRAINT FK_FEA3E481D0B0256D FOREIGN KEY (eval_social_person_id) REFERENCES eval_social_person (id)');
        $this->addSql('ALTER TABLE evaluation_person ADD CONSTRAINT FK_FEA3E481A4A60DFC FOREIGN KEY (eval_justice_person_id) REFERENCES eval_justice_person (id)');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_FEA3E481AACB6D44 ON evaluation_person (eval_adm_person_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FEA3E481338AD0BA ON evaluation_person (eval_budget_person_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FEA3E4812B28E277 ON evaluation_person (eval_family_person_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FEA3E481A526B42D ON evaluation_person (eval_prof_person_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FEA3E481D0B0256D ON evaluation_person (eval_social_person_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FEA3E481A4A60DFC ON evaluation_person (eval_justice_person_id)');

        $this->addSql('UPDATE evaluation_group LEFT JOIN eval_social_group
            ON evaluation_group.id = eval_social_group.evaluation_group_id 
            SET evaluation_group.eval_social_group_id = eval_social_group.id
        ');
        $this->addSql('UPDATE evaluation_group LEFT JOIN eval_family_group
            ON evaluation_group.id = eval_family_group.evaluation_group_id 
            SET evaluation_group.eval_family_group_id = eval_family_group.id
        ');
        $this->addSql('UPDATE evaluation_group LEFT JOIN eval_housing_group
            ON evaluation_group.id = eval_housing_group.evaluation_group_id 
            SET evaluation_group.eval_housing_group_id = eval_housing_group.id
        ');
        $this->addSql('UPDATE evaluation_group LEFT JOIN eval_budget_group
            ON evaluation_group.id = eval_budget_group.evaluation_group_id 
            SET evaluation_group.eval_budget_group_id = eval_budget_group.id
        ');
        $this->addSql('UPDATE evaluation_group LEFT JOIN eval_hotel_life_group
            ON evaluation_group.id = eval_hotel_life_group.evaluation_group_id 
            SET evaluation_group.eval_hotel_life_group_id = eval_hotel_life_group.id
        ');

        $this->addSql('UPDATE evaluation_person LEFT JOIN eval_adm_person
            ON evaluation_person.id = eval_adm_person.evaluation_person_id 
            SET evaluation_person.eval_adm_person_id = eval_adm_person.id
        ');
        $this->addSql('UPDATE evaluation_person LEFT JOIN eval_budget_person
            ON evaluation_person.id = eval_budget_person.evaluation_person_id 
            SET evaluation_person.eval_budget_person_id = eval_budget_person.id
        ');
        $this->addSql('UPDATE evaluation_person LEFT JOIN eval_family_person
            ON evaluation_person.id = eval_family_person.evaluation_person_id 
            SET evaluation_person.eval_family_person_id = eval_family_person.id
        ');
        $this->addSql('UPDATE evaluation_person LEFT JOIN eval_social_person
            ON evaluation_person.id = eval_social_person.evaluation_person_id 
            SET evaluation_person.eval_social_person_id = eval_social_person.id
        ');
        $this->addSql('UPDATE evaluation_person LEFT JOIN eval_prof_person
            ON evaluation_person.id = eval_prof_person.evaluation_person_id 
            SET evaluation_person.eval_prof_person_id = eval_prof_person.id
        ');
        $this->addSql('UPDATE evaluation_person LEFT JOIN eval_justice_person
            ON evaluation_person.id = eval_justice_person.evaluation_person_id 
            SET evaluation_person.eval_justice_person_id = eval_justice_person.id
        ');

        $this->addSql('ALTER TABLE eval_adm_person DROP FOREIGN KEY FK_857BE8CBF37B70C3');
        $this->addSql('DROP INDEX UNIQ_857BE8CBF37B70C3 ON eval_adm_person');
        $this->addSql('ALTER TABLE eval_adm_person DROP evaluation_person_id');
        $this->addSql('ALTER TABLE eval_budget_group DROP FOREIGN KEY FK_CC40E5D5F31AA061');
        $this->addSql('DROP INDEX UNIQ_CC40E5D5F31AA061 ON eval_budget_group');
        $this->addSql('ALTER TABLE eval_budget_group DROP evaluation_group_id');
        $this->addSql('ALTER TABLE eval_budget_person DROP FOREIGN KEY FK_29CA41B3F37B70C3');
        $this->addSql('DROP INDEX UNIQ_29CA41B3F37B70C3 ON eval_budget_person');
        $this->addSql('ALTER TABLE eval_budget_person DROP evaluation_person_id');
        $this->addSql('ALTER TABLE eval_family_group DROP FOREIGN KEY FK_9B26E350F31AA061');
        $this->addSql('DROP INDEX UNIQ_9B26E350F31AA061 ON eval_family_group');
        $this->addSql('ALTER TABLE eval_family_group DROP evaluation_group_id');
        $this->addSql('ALTER TABLE eval_family_person DROP FOREIGN KEY FK_B44F501AF37B70C3');
        $this->addSql('DROP INDEX UNIQ_B44F501AF37B70C3 ON eval_family_person');
        $this->addSql('ALTER TABLE eval_family_person DROP evaluation_person_id');
        $this->addSql('ALTER TABLE eval_hotel_life_group DROP FOREIGN KEY FK_F7E85143F31AA061');
        $this->addSql('DROP INDEX UNIQ_F7E85143F31AA061 ON eval_hotel_life_group');
        $this->addSql('ALTER TABLE eval_hotel_life_group DROP evaluation_group_id');
        $this->addSql('ALTER TABLE eval_housing_group DROP FOREIGN KEY FK_F760547CF31AA061');
        $this->addSql('DROP INDEX UNIQ_F760547CF31AA061 ON eval_housing_group');
        $this->addSql('ALTER TABLE eval_housing_group DROP evaluation_group_id');
        $this->addSql('ALTER TABLE eval_justice_person DROP FOREIGN KEY FK_9CA12E56F37B70C3');
        $this->addSql('DROP INDEX UNIQ_9CA12E56F37B70C3 ON eval_justice_person');
        $this->addSql('ALTER TABLE eval_justice_person DROP evaluation_person_id');
        $this->addSql('ALTER TABLE eval_prof_person DROP FOREIGN KEY FK_8932D489F37B70C3');
        $this->addSql('DROP INDEX UNIQ_8932D489F37B70C3 ON eval_prof_person');
        $this->addSql('ALTER TABLE eval_prof_person DROP evaluation_person_id');
        $this->addSql('ALTER TABLE eval_social_group DROP FOREIGN KEY FK_EDC1C4B1F31AA061');
        $this->addSql('DROP INDEX UNIQ_EDC1C4B1F31AA061 ON eval_social_group');
        $this->addSql('ALTER TABLE eval_social_group DROP evaluation_group_id');
        $this->addSql('ALTER TABLE eval_social_person DROP FOREIGN KEY FK_633465D3F37B70C3');
        $this->addSql('DROP INDEX UNIQ_633465D3F37B70C3 ON eval_social_person');
        $this->addSql('ALTER TABLE eval_social_person DROP evaluation_person_id');

        $this->addSql('ALTER TABLE evaluation_group DROP FOREIGN KEY FK_A8373934EC74FA8D');
        $this->addSql('DROP INDEX IDX_A8373934EC74FA8D ON evaluation_group');
        $this->addSql('ALTER TABLE evaluation_group DROP eval_init_group_id');

        $this->addSql('ALTER TABLE evaluation_person DROP FOREIGN KEY FK_FEA3E4815AD8B0CA');
        $this->addSql('DROP INDEX IDX_FEA3E4815AD8B0CA ON evaluation_person');
        $this->addSql('ALTER TABLE evaluation_person DROP eval_init_person_id');

        $this->addSql('ALTER TABLE eval_init_resource CHANGE eval_init_person_id eval_init_person_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE hotel_support ADD priority_criteria LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE service ADD location_id VARCHAR(255) DEFAULT NULL, ADD lat DOUBLE PRECISION DEFAULT NULL, ADD lon DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE setting ADD address VARCHAR(255) DEFAULT NULL, ADD city VARCHAR(255) DEFAULT NULL, ADD zipCode VARCHAR(10) DEFAULT NULL, ADD comment_location VARCHAR(255) DEFAULT NULL, ADD location_id VARCHAR(255) DEFAULT NULL, ADD lat DOUBLE PRECISION DEFAULT NULL, ADD lon DOUBLE PRECISION DEFAULT NULL');
    
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avdl ADD support_group_id INT NOT NULL');
        $this->addSql('ALTER TABLE avdl ADD CONSTRAINT FK_B737D8484AE25278 FOREIGN KEY (support_group_id) REFERENCES support_group (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B737D8484AE25278 ON avdl (support_group_id)');
        $this->addSql('ALTER TABLE eval_init_group ADD support_group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_init_group ADD CONSTRAINT FK_53AF32384AE25278 FOREIGN KEY (support_group_id) REFERENCES support_group (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_995638124AE25278 ON eval_init_group (support_group_id)');
        $this->addSql('ALTER TABLE hotel_support ADD support_group_id INT NOT NULL');
        $this->addSql('ALTER TABLE hotel_support ADD CONSTRAINT FK_534EF58C4AE25278 FOREIGN KEY (support_group_id) REFERENCES support_group (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_534EF58C4AE25278 ON hotel_support (support_group_id)');
        $this->addSql('ALTER TABLE origin_request ADD support_group_id INT NOT NULL');
        $this->addSql('ALTER TABLE origin_request ADD CONSTRAINT FK_8EE09AF24AE25278 FOREIGN KEY (support_group_id) REFERENCES support_group (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8EE09AF24AE25278 ON origin_request (support_group_id)');
        $this->addSql('ALTER TABLE support_group DROP FOREIGN KEY FK_9F7A521DEC74FA8D');
        $this->addSql('ALTER TABLE support_group DROP FOREIGN KEY FK_9F7A521DFBA5E2E8');
        $this->addSql('ALTER TABLE support_group DROP FOREIGN KEY FK_9F7A521DE994F3E7');
        $this->addSql('ALTER TABLE support_group DROP FOREIGN KEY FK_9F7A521D76C15EE8');
        $this->addSql('DROP INDEX UNIQ_9F7A521DEC74FA8D ON support_group');
        $this->addSql('DROP INDEX UNIQ_9F7A521DFBA5E2E8 ON support_group');
        $this->addSql('DROP INDEX UNIQ_9F7A521DE994F3E7 ON support_group');
        $this->addSql('DROP INDEX UNIQ_9F7A521D76C15EE8 ON support_group');
        $this->addSql('ALTER TABLE support_group DROP eval_init_group_id, DROP origin_request_id, DROP avdl_id, DROP hotel_support_id');

        $this->addSql('ALTER TABLE eval_init_person ADD support_person_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_init_person ADD CONSTRAINT FK_F7EE30A197A920F1 FOREIGN KEY (support_person_id) REFERENCES support_person (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2C9F007D97A920F1 ON eval_init_person (support_person_id)');
        $this->addSql('ALTER TABLE hotel_support DROP priority_criteria');
        $this->addSql('ALTER TABLE support_person DROP FOREIGN KEY FK_BC2631865AD8B0CA');
        $this->addSql('DROP INDEX UNIQ_BC2631865AD8B0CA ON support_person');
        $this->addSql('ALTER TABLE support_person DROP eval_init_person_id');

        $this->addSql('ALTER TABLE avdl ADD support_group_id INT NOT NULL');
        $this->addSql('ALTER TABLE avdl ADD CONSTRAINT FK_B737D8484AE25278 FOREIGN KEY (support_group_id) REFERENCES support_group (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B737D8484AE25278 ON avdl (support_group_id)');
        $this->addSql('ALTER TABLE eval_init_group ADD support_group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_init_group ADD CONSTRAINT FK_53AF32384AE25278 FOREIGN KEY (support_group_id) REFERENCES support_group (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_995638124AE25278 ON eval_init_group (support_group_id)');
        $this->addSql('ALTER TABLE hotel_support ADD support_group_id INT NOT NULL');
        $this->addSql('ALTER TABLE hotel_support ADD CONSTRAINT FK_534EF58C4AE25278 FOREIGN KEY (support_group_id) REFERENCES support_group (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_534EF58C4AE25278 ON hotel_support (support_group_id)');
        $this->addSql('ALTER TABLE origin_request ADD support_group_id INT NOT NULL');
        $this->addSql('ALTER TABLE origin_request ADD CONSTRAINT FK_8EE09AF24AE25278 FOREIGN KEY (support_group_id) REFERENCES support_group (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8EE09AF24AE25278 ON origin_request (support_group_id)');
        $this->addSql('ALTER TABLE support_group DROP FOREIGN KEY FK_9F7A521DEC74FA8D');
        $this->addSql('ALTER TABLE support_group DROP FOREIGN KEY FK_9F7A521DFBA5E2E8');
        $this->addSql('ALTER TABLE support_group DROP FOREIGN KEY FK_9F7A521DE994F3E7');
        $this->addSql('ALTER TABLE support_group DROP FOREIGN KEY FK_9F7A521D76C15EE8');
        $this->addSql('DROP INDEX UNIQ_9F7A521DEC74FA8D ON support_group');
        $this->addSql('DROP INDEX UNIQ_9F7A521DFBA5E2E8 ON support_group');
        $this->addSql('DROP INDEX UNIQ_9F7A521DE994F3E7 ON support_group');
        $this->addSql('DROP INDEX UNIQ_9F7A521D76C15EE8 ON support_group');
        $this->addSql('ALTER TABLE support_group DROP eval_init_group_id, DROP origin_request_id, DROP avdl_id, DROP hotel_support_id');

        $this->addSql('ALTER TABLE eval_init_person ADD support_person_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_init_person ADD CONSTRAINT FK_F7EE30A197A920F1 FOREIGN KEY (support_person_id) REFERENCES support_person (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2C9F007D97A920F1 ON eval_init_person (support_person_id)');
        $this->addSql('ALTER TABLE hotel_support DROP priority_criteria');
        $this->addSql('ALTER TABLE support_person DROP FOREIGN KEY FK_BC2631865AD8B0CA');
        $this->addSql('DROP INDEX UNIQ_BC2631865AD8B0CA ON support_person');
        $this->addSql('ALTER TABLE support_person DROP eval_init_person_id');

        $this->addSql('ALTER TABLE eval_adm_person ADD evaluation_person_id INT NOT NULL');
        $this->addSql('ALTER TABLE eval_adm_person ADD CONSTRAINT FK_857BE8CBF37B70C3 FOREIGN KEY (evaluation_person_id) REFERENCES evaluation_person (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_857BE8CBF37B70C3 ON eval_adm_person (evaluation_person_id)');
        $this->addSql('ALTER TABLE eval_budget_group ADD evaluation_group_id INT NOT NULL');
        $this->addSql('ALTER TABLE eval_budget_group ADD CONSTRAINT FK_CC40E5D5F31AA061 FOREIGN KEY (evaluation_group_id) REFERENCES evaluation_group (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_CC40E5D5F31AA061 ON eval_budget_group (evaluation_group_id)');
        $this->addSql('ALTER TABLE eval_budget_person ADD evaluation_person_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_budget_person ADD CONSTRAINT FK_29CA41B3F37B70C3 FOREIGN KEY (evaluation_person_id) REFERENCES evaluation_person (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_29CA41B3F37B70C3 ON eval_budget_person (evaluation_person_id)');
        $this->addSql('ALTER TABLE eval_family_group ADD evaluation_group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_family_group ADD CONSTRAINT FK_9B26E350F31AA061 FOREIGN KEY (evaluation_group_id) REFERENCES evaluation_group (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9B26E350F31AA061 ON eval_family_group (evaluation_group_id)');
        $this->addSql('ALTER TABLE eval_family_person ADD evaluation_person_id INT NOT NULL');
        $this->addSql('ALTER TABLE eval_family_person ADD CONSTRAINT FK_B44F501AF37B70C3 FOREIGN KEY (evaluation_person_id) REFERENCES evaluation_person (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B44F501AF37B70C3 ON eval_family_person (evaluation_person_id)');
        $this->addSql('ALTER TABLE eval_hotel_life_group ADD evaluation_group_id INT NOT NULL');
        $this->addSql('ALTER TABLE eval_hotel_life_group ADD CONSTRAINT FK_F7E85143F31AA061 FOREIGN KEY (evaluation_group_id) REFERENCES evaluation_group (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F7E85143F31AA061 ON eval_hotel_life_group (evaluation_group_id)');
        $this->addSql('ALTER TABLE eval_housing_group ADD evaluation_group_id INT NOT NULL');
        $this->addSql('ALTER TABLE eval_housing_group ADD CONSTRAINT FK_F760547CF31AA061 FOREIGN KEY (evaluation_group_id) REFERENCES evaluation_group (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F760547CF31AA061 ON eval_housing_group (evaluation_group_id)');
        $this->addSql('ALTER TABLE eval_justice_person ADD evaluation_person_id INT NOT NULL');
        $this->addSql('ALTER TABLE eval_justice_person ADD CONSTRAINT FK_9CA12E56F37B70C3 FOREIGN KEY (evaluation_person_id) REFERENCES evaluation_person (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9CA12E56F37B70C3 ON eval_justice_person (evaluation_person_id)');
        $this->addSql('ALTER TABLE eval_prof_person ADD evaluation_person_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_prof_person ADD CONSTRAINT FK_8932D489F37B70C3 FOREIGN KEY (evaluation_person_id) REFERENCES evaluation_person (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8932D489F37B70C3 ON eval_prof_person (evaluation_person_id)');
        $this->addSql('ALTER TABLE eval_social_group ADD evaluation_group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_social_group ADD CONSTRAINT FK_EDC1C4B1F31AA061 FOREIGN KEY (evaluation_group_id) REFERENCES evaluation_group (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_EDC1C4B1F31AA061 ON eval_social_group (evaluation_group_id)');
        $this->addSql('ALTER TABLE eval_social_person ADD evaluation_person_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_social_person ADD CONSTRAINT FK_633465D3F37B70C3 FOREIGN KEY (evaluation_person_id) REFERENCES evaluation_person (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_633465D3F37B70C3 ON eval_social_person (evaluation_person_id)');
        $this->addSql('ALTER TABLE evaluation_group DROP FOREIGN KEY FK_A8373934E128B196');
        $this->addSql('ALTER TABLE evaluation_group DROP FOREIGN KEY FK_A8373934ADA3CEC9');
        $this->addSql('ALTER TABLE evaluation_group DROP FOREIGN KEY FK_A8373934A8CFADFB');
        $this->addSql('ALTER TABLE evaluation_group DROP FOREIGN KEY FK_A8373934B8D6741E');
        $this->addSql('ALTER TABLE evaluation_group DROP FOREIGN KEY FK_A837393419ECF001');
        $this->addSql('DROP INDEX UNIQ_A8373934E128B196 ON evaluation_group');
        $this->addSql('DROP INDEX UNIQ_A8373934ADA3CEC9 ON evaluation_group');
        $this->addSql('DROP INDEX UNIQ_A8373934A8CFADFB ON evaluation_group');
        $this->addSql('DROP INDEX UNIQ_A8373934B8D6741E ON evaluation_group');
        $this->addSql('DROP INDEX UNIQ_A837393419ECF001 ON evaluation_group');
        $this->addSql('ALTER TABLE evaluation_group DROP eval_social_group_id, DROP eval_family_group_id, DROP eval_housing_group_id, DROP eval_budget_group_id, DROP eval_hotel_life_group_id');
        $this->addSql('ALTER TABLE evaluation_person DROP FOREIGN KEY FK_FEA3E481AACB6D44');
        $this->addSql('ALTER TABLE evaluation_person DROP FOREIGN KEY FK_FEA3E481338AD0BA');
        $this->addSql('ALTER TABLE evaluation_person DROP FOREIGN KEY FK_FEA3E4812B28E277');
        $this->addSql('ALTER TABLE evaluation_person DROP FOREIGN KEY FK_FEA3E481A526B42D');
        $this->addSql('ALTER TABLE evaluation_person DROP FOREIGN KEY FK_FEA3E481D0B0256D');
        $this->addSql('ALTER TABLE evaluation_person DROP FOREIGN KEY FK_FEA3E481A4A60DFC');
        $this->addSql('DROP INDEX UNIQ_FEA3E481AACB6D44 ON evaluation_person');
        $this->addSql('DROP INDEX UNIQ_FEA3E481338AD0BA ON evaluation_person');
        $this->addSql('DROP INDEX UNIQ_FEA3E4812B28E277 ON evaluation_person');
        $this->addSql('DROP INDEX UNIQ_FEA3E481A526B42D ON evaluation_person');
        $this->addSql('DROP INDEX UNIQ_FEA3E481D0B0256D ON evaluation_person');
        $this->addSql('DROP INDEX UNIQ_FEA3E481A4A60DFC ON evaluation_person');
        $this->addSql('ALTER TABLE evaluation_person DROP eval_adm_person_id, DROP eval_budget_person_id, DROP eval_family_person_id, DROP eval_prof_person_id, DROP eval_social_person_id, DROP eval_justice_person_id');

        $this->addSql('ALTER TABLE evaluation_group ADD eval_init_group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE evaluation_group ADD CONSTRAINT FK_A8373934EC74FA8D FOREIGN KEY (eval_init_group_id) REFERENCES eval_init_group (id)');
        $this->addSql('CREATE INDEX IDX_A8373934EC74FA8D ON evaluation_group (eval_init_group_id)');

        $this->addSql('ALTER TABLE evaluation_person ADD eval_init_person_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE evaluation_person ADD CONSTRAINT FK_FEA3E4815AD8B0CA FOREIGN KEY (eval_init_person_id) REFERENCES eval_init_person (id)');
        $this->addSql('CREATE INDEX IDX_FEA3E4815AD8B0CA ON evaluation_person (eval_init_person_id)');

        $this->addSql('ALTER TABLE eval_init_resource CHANGE eval_init_person_id eval_init_person_id INT NOT NULL');

        $this->addSql('ALTER TABLE hotel_support DROP priority_criteria');
        $this->addSql('ALTER TABLE service DROP location_id, DROP lat, DROP lon');
        $this->addSql('ALTER TABLE setting DROP address, DROP city, DROP zipCode, DROP comment_location, DROP location_id, DROP lat, DROP lon');
   
    }
}
