<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191011080227 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE group_people ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL, DROP created_by, DROP updated_by');
        $this->addSql('ALTER TABLE group_people ADD CONSTRAINT FK_FB90B2F6B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE group_people ADD CONSTRAINT FK_FB90B2F6896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_FB90B2F6B03A8386 ON group_people (created_by_id)');
        $this->addSql('CREATE INDEX IDX_FB90B2F6896DBBDE ON group_people (updated_by_id)');
        $this->addSql('ALTER TABLE person ADD created_by_id INT DEFAULT NULL, ADD updated_by_id INT DEFAULT NULL, DROP created_by, DROP updated_by');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD176B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD176896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_34DCD176B03A8386 ON person (created_by_id)');
        $this->addSql('CREATE INDEX IDX_34DCD176896DBBDE ON person (updated_by_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE group_people DROP FOREIGN KEY FK_FB90B2F6B03A8386');
        $this->addSql('ALTER TABLE group_people DROP FOREIGN KEY FK_FB90B2F6896DBBDE');
        $this->addSql('DROP INDEX IDX_FB90B2F6B03A8386 ON group_people');
        $this->addSql('DROP INDEX IDX_FB90B2F6896DBBDE ON group_people');
        $this->addSql('ALTER TABLE group_people ADD created_by INT DEFAULT NULL, ADD updated_by INT DEFAULT NULL, DROP created_by_id, DROP updated_by_id');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176B03A8386');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176896DBBDE');
        $this->addSql('DROP INDEX IDX_34DCD176B03A8386 ON person');
        $this->addSql('DROP INDEX IDX_34DCD176896DBBDE ON person');
        $this->addSql('ALTER TABLE person ADD created_by INT DEFAULT NULL, ADD updated_by INT DEFAULT NULL, DROP created_by_id, DROP updated_by_id');
    }
}
