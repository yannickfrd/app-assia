<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191127145324 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sit_budget_grp (id INT AUTO_INCREMENT NOT NULL, support_grp_id INT NOT NULL, ressources_grp_amt INT DEFAULT NULL, charges_grp_amt INT DEFAULT NULL, debts_grp_amt INT DEFAULT NULL, tax_income_n1amt INT DEFAULT NULL, tax_income_n2amt INT DEFAULT NULL, comment_sit_budget LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_B0DE52845DE471A0 (support_grp_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sit_budget_grp ADD CONSTRAINT FK_B0DE52845DE471A0 FOREIGN KEY (support_grp_id) REFERENCES support_grp (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sit_budget_grp');
    }
}
