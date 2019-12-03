<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191203152728 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE support_pers DROP FOREIGN KEY FK_874DBEA6BF74EF3');
        $this->addSql('DROP INDEX UNIQ_874DBEA6BF74EF3 ON support_pers');
        $this->addSql('ALTER TABLE support_pers DROP sit_budget_id');
        $this->addSql('ALTER TABLE sit_family_pers CHANGE support_pers_id support_pers_id INT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sit_family_pers CHANGE support_pers_id support_pers_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE support_pers ADD sit_budget_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE support_pers ADD CONSTRAINT FK_874DBEA6BF74EF3 FOREIGN KEY (sit_budget_id) REFERENCES sit_budget (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_874DBEA6BF74EF3 ON support_pers (sit_budget_id)');
    }
}
