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
        return 'Rename "init_eval_group" to "eval_init_group", "init_eval_person" to "eval_init_person" and 
            update "eval_budget_person" table (rename "resources" to resource, "charges" to "charge" and "debts" to "debt")';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE init_eval_group RENAME TO eval_init_group');
        $this->addSql('ALTER TABLE init_eval_person RENAME TO eval_init_person');

        $this->addSql('ALTER TABLE eval_budget_person CHANGE resources resource SMALLINT DEFAULT NULL, CHANGE charges charge SMALLINT DEFAULT NULL, CHANGE debts debt SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_init_person CHANGE resources resource SMALLINT DEFAULT NULL, CHANGE debts debt SMALLINT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE eval_init_group RENAME TO init_eval_group');
        $this->addSql('ALTER TABLE eval_init_person RENAME TO init_eval_person');

        $this->addSql('ALTER TABLE eval_budget_person CHANGE debt debts SMALLINT DEFAULT NULL, CHANGE resource resources SMALLINT DEFAULT NULL, CHANGE charge charges SMALLINT DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_init_person CHANGE resource resources SMALLINT DEFAULT NULL CHANGE debt debts SMALLINT DEFAULT NULL');
    }
}
