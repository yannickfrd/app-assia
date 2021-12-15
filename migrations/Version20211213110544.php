<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211213110544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE migration_versions');
        $this->addSql('ALTER TABLE eval_family_person ADD pmi_follow_up SMALLINT DEFAULT NULL, ADD pmi_name VARCHAR(255) DEFAULT NULL, ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE payment RENAME INDEX idx_ea351e154ae25278 TO IDX_6D28840D4AE25278');
        $this->addSql('ALTER TABLE payment RENAME INDEX idx_ea351e15b03a8386 TO IDX_6D28840DB03A8386');
        $this->addSql('ALTER TABLE payment RENAME INDEX idx_ea351e15896dbbde TO IDX_6D28840D896DBBDE');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
        $this->addSql('ALTER TABLE avdl ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_adm_person ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_budget_group ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_budget_person ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_family_group ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_hotel_life_group ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_housing_group ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_justice_person ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_prof_person ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_social_group ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_social_person ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE hotel_support ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE init_eval_group ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE init_eval_person ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE note ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE organization ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE origin_request ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE payment ADD deleted_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE rdv ADD deleted_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE migration_versions (version VARCHAR(14) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, executed_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(version)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE eval_family_person DROP pmi_follow_up, DROP pmi_name, DROP deleted_at');
        $this->addSql('ALTER TABLE payment RENAME INDEX idx_6d28840db03a8386 TO IDX_EA351E15B03A8386');
        $this->addSql('ALTER TABLE payment RENAME INDEX idx_6d28840d896dbbde TO IDX_EA351E15896DBBDE');
        $this->addSql('ALTER TABLE payment RENAME INDEX idx_6d28840d4ae25278 TO IDX_EA351E154AE25278');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE avdl DROP deleted_at');
        $this->addSql('ALTER TABLE eval_adm_person DROP deleted_at');
        $this->addSql('ALTER TABLE eval_budget_group DROP deleted_at');
        $this->addSql('ALTER TABLE eval_budget_person DROP deleted_at');
        $this->addSql('ALTER TABLE eval_family_group DROP deleted_at');
        $this->addSql('ALTER TABLE eval_hotel_life_group DROP deleted_at');
        $this->addSql('ALTER TABLE eval_housing_group DROP deleted_at');
        $this->addSql('ALTER TABLE eval_justice_person DROP deleted_at');
        $this->addSql('ALTER TABLE eval_prof_person DROP deleted_at');
        $this->addSql('ALTER TABLE eval_social_group DROP deleted_at');
        $this->addSql('ALTER TABLE eval_social_person DROP deleted_at');
        $this->addSql('ALTER TABLE hotel_support DROP deleted_at');
        $this->addSql('ALTER TABLE init_eval_group DROP deleted_at');
        $this->addSql('ALTER TABLE init_eval_person DROP deleted_at');
        $this->addSql('ALTER TABLE note DROP deleted_at');
        $this->addSql('ALTER TABLE organization DROP deleted_at');
        $this->addSql('ALTER TABLE origin_request DROP deleted_at');
        $this->addSql('ALTER TABLE payment DROP deleted_at');
        $this->addSql('ALTER TABLE rdv DROP deleted_at');
    }
}
