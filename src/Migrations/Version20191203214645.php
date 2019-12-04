<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191203214645 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE support_grp ADD referent_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE support_grp ADD CONSTRAINT FK_E468E75735E47E35 FOREIGN KEY (referent_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_E468E75735E47E35 ON support_grp (referent_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE support_grp DROP FOREIGN KEY FK_E468E75735E47E35');
        $this->addSql('DROP INDEX IDX_E468E75735E47E35 ON support_grp');
        $this->addSql('ALTER TABLE support_grp DROP referent_id');
    }
}
