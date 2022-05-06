<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220504133719 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename field "sended" to "sent" into table "alert"';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE alert CHANGE sended sent TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE alert CHANGE sent send TINYINT(1) NOT NULL');
    }
}
