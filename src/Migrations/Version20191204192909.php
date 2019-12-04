<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191204192909 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE service_user (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, service_id INT DEFAULT NULL, role INT NOT NULL, INDEX IDX_43D062A5A76ED395 (user_id), INDEX IDX_43D062A5ED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE service_user ADD CONSTRAINT FK_43D062A5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE service_user ADD CONSTRAINT FK_43D062A5ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('DROP TABLE role_user');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE role_user (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, service_id INT DEFAULT NULL, role INT NOT NULL, INDEX IDX_332CA4DDED5CA9E6 (service_id), INDEX IDX_332CA4DDA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE role_user ADD CONSTRAINT FK_332CA4DDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE role_user ADD CONSTRAINT FK_332CA4DDED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
        $this->addSql('DROP TABLE service_user');
    }
}
