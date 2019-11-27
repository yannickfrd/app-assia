<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191127194003 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sit_budget ADD ressource_other TINYINT(1) DEFAULT NULL, ADD ressource_other_precision VARCHAR(255) DEFAULT NULL, ADD ressource_other_amt DOUBLE PRECISION DEFAULT NULL, ADD charge_other TINYINT(1) DEFAULT NULL, ADD charge_other_precision VARCHAR(255) DEFAULT NULL, ADD charge_other_amt DOUBLE PRECISION DEFAULT NULL, DROP other_income, DROP other_income_precision, DROP other_income_amt, DROP other_charge, DROP other_charge_precision, DROP other_charge_amt');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sit_budget ADD other_income TINYINT(1) DEFAULT NULL, ADD other_income_precision VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD other_income_amt DOUBLE PRECISION DEFAULT NULL, ADD other_charge TINYINT(1) DEFAULT NULL, ADD other_charge_precision VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD other_charge_amt DOUBLE PRECISION DEFAULT NULL, DROP ressource_other, DROP ressource_other_precision, DROP ressource_other_amt, DROP charge_other, DROP charge_other_precision, DROP charge_other_amt');
    }
}
