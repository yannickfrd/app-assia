<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211222091527 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create resource, "charge" and "debt" tables and update eval_budget_person table (rename "resources" to resource, "charges" to "charge" and "debts" to "debt")';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE eval_budget_resource (id INT AUTO_INCREMENT NOT NULL, eval_budget_person_id INT DEFAULT NULL, end_date DATE DEFAULT NULL, type SMALLINT NOT NULL, amount DOUBLE PRECISION DEFAULT NULL, comment VARCHAR(255) DEFAULT NULL, INDEX IDX_BC91F416338AD0BA (eval_budget_person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE eval_budget_charge (id INT AUTO_INCREMENT NOT NULL, eval_budget_person_id INT DEFAULT NULL, type SMALLINT NOT NULL, amount DOUBLE PRECISION DEFAULT NULL, comment VARCHAR(255) DEFAULT NULL, INDEX IDX_556BA434338AD0BA (eval_budget_person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE eval_budget_debt (id INT AUTO_INCREMENT NOT NULL, eval_budget_person_id INT DEFAULT NULL, type SMALLINT NOT NULL, amount DOUBLE PRECISION DEFAULT NULL, comment VARCHAR(255) DEFAULT NULL, INDEX IDX_DBBF0A83338AD0BA (eval_budget_person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE eval_init_resource (id INT AUTO_INCREMENT NOT NULL, init_eval_person_id INT NOT NULL, type SMALLINT NOT NULL, amount DOUBLE PRECISION DEFAULT NULL, comment VARCHAR(255) DEFAULT NULL, INDEX IDX_E71201A5260F0B1C (init_eval_person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE eval_budget_person CHANGE resources resource SMALLINT DEFAULT NULL, CHANGE charges charge SMALLINT DEFAULT NULL, CHANGE debts debt SMALLINT DEFAULT NULL');
        $this->addSql('CREATE TABLE resource (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, code INT NOT NULL, type INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE eval_budget_resource ADD resource_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE init_eval_person CHANGE resources resource SMALLINT DEFAULT NULL, CHANGE debts debt SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE init_eval_group RENAME TO eval_init_group');
        $this->addSql('ALTER TABLE init_eval_person RENAME TO eval_init_person');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE eval_budget_resource');
        $this->addSql('DROP TABLE eval_budget_charge');
        $this->addSql('DROP TABLE eval_budget_debt');
        $this->addSql('DROP TABLE eval_init_resource');
        $this->addSql('ALTER TABLE eval_budget_person CHANGE debt debts SMALLINT DEFAULT NULL, CHANGE resource resources SMALLINT DEFAULT NULL, CHANGE charge charges SMALLINT DEFAULT NULL');
        $this->addSql('DROP TABLE resource');
        $this->addSql('ALTER TABLE init_eval_person CHANGE resource resources SMALLINT DEFAULT NULL CHANGE debt debts SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_init_group RENAME TO init_eval_group');
        $this->addSql('ALTER TABLE eval_init_person RENAME TO init_eval_person');
        $this->addSql('ALTER TABLE eval_budget_resource DROP resource_id');
    }
}
