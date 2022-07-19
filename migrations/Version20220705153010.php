<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220705153010 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add archivedAt fields and remove hard_deletion_delay for service table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE people_group ADD archived_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE person ADD archived_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE service_setting DROP hard_deletion_delay');
        $this->addSql('ALTER TABLE support_group ADD archived_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE people_group DROP archived_at');
        $this->addSql('ALTER TABLE person DROP archived_at');
        $this->addSql('ALTER TABLE service_setting ADD hard_deletion_delay INT DEFAULT NULL');
        $this->addSql('ALTER TABLE support_group DROP archived_at');
    }
}
