<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191025222041 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE department (id INT AUTO_INCREMENT NOT NULL, pole_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, INDEX IDX_CD1DE18A419C3385 (pole_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE pole (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role_user (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, department_id INT DEFAULT NULL, role INT NOT NULL, INDEX IDX_332CA4DDA76ED395 (user_id), INDEX IDX_332CA4DDAE80F5DF (department_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE department ADD CONSTRAINT FK_CD1DE18A419C3385 FOREIGN KEY (pole_id) REFERENCES pole (id)');
        $this->addSql('ALTER TABLE role_user ADD CONSTRAINT FK_332CA4DDA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE role_user ADD CONSTRAINT FK_332CA4DDAE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE social_support_group ADD department_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE social_support_group ADD CONSTRAINT FK_29C0DC89AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('CREATE INDEX IDX_29C0DC89AE80F5DF ON social_support_group (department_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE role_user DROP FOREIGN KEY FK_332CA4DDAE80F5DF');
        $this->addSql('ALTER TABLE social_support_group DROP FOREIGN KEY FK_29C0DC89AE80F5DF');
        $this->addSql('ALTER TABLE department DROP FOREIGN KEY FK_CD1DE18A419C3385');
        $this->addSql('DROP TABLE department');
        $this->addSql('DROP TABLE pole');
        $this->addSql('DROP TABLE role_user');
        $this->addSql('DROP INDEX IDX_29C0DC89AE80F5DF ON social_support_group');
        $this->addSql('ALTER TABLE social_support_group DROP department_id');
    }
}
