<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191222175154 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE note CHANGE support_group_id support_group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE rdv DROP FOREIGN KEY FK_10C31F86A76ED395');
        $this->addSql('DROP INDEX IDX_10C31F86A76ED395 ON rdv');
        $this->addSql('ALTER TABLE rdv ADD start DATETIME NOT NULL, ADD end DATETIME NOT NULL, DROP user_id, DROP start_date, DROP start_time, DROP end_time, CHANGE support_group_id support_group_id INT DEFAULT NULL, CHANGE status status SMALLINT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE note CHANGE support_group_id support_group_id INT NOT NULL');
        $this->addSql('ALTER TABLE rdv ADD user_id INT NOT NULL, ADD start_date DATE NOT NULL, ADD start_time TIME NOT NULL, ADD end_time TIME NOT NULL, DROP start, DROP end, CHANGE support_group_id support_group_id INT NOT NULL, CHANGE status status SMALLINT NOT NULL');
        $this->addSql('ALTER TABLE rdv ADD CONSTRAINT FK_10C31F86A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_10C31F86A76ED395 ON rdv (user_id)');
    }
}
