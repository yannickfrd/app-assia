<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191026100125 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE social_support_person DROP FOREIGN KEY FK_4BF2DC553E4CADB5');
        $this->addSql('CREATE TABLE social_support_grp (id INT AUTO_INCREMENT NOT NULL, group_people_id INT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, department_id INT DEFAULT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, status INT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_91CC1779F1F495D7 (group_people_id), INDEX IDX_91CC1779B03A8386 (created_by_id), INDEX IDX_91CC1779896DBBDE (updated_by_id), INDEX IDX_91CC1779AE80F5DF (department_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE social_support_pers (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, social_support_grp_id INT NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, status INT NOT NULL, comment LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_D4D772D5217BBB47 (person_id), INDEX IDX_D4D772D5AA309C73 (social_support_grp_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE social_support_grp ADD CONSTRAINT FK_91CC1779F1F495D7 FOREIGN KEY (group_people_id) REFERENCES group_people (id)');
        $this->addSql('ALTER TABLE social_support_grp ADD CONSTRAINT FK_91CC1779B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE social_support_grp ADD CONSTRAINT FK_91CC1779896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE social_support_grp ADD CONSTRAINT FK_91CC1779AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE social_support_pers ADD CONSTRAINT FK_D4D772D5217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE social_support_pers ADD CONSTRAINT FK_D4D772D5AA309C73 FOREIGN KEY (social_support_grp_id) REFERENCES social_support_grp (id)');
        $this->addSql('DROP TABLE social_support_group');
        $this->addSql('DROP TABLE social_support_person');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE social_support_pers DROP FOREIGN KEY FK_D4D772D5AA309C73');
        $this->addSql('CREATE TABLE social_support_group (id INT AUTO_INCREMENT NOT NULL, group_people_id INT NOT NULL, created_by_id INT DEFAULT NULL, updated_by_id INT DEFAULT NULL, department_id INT DEFAULT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, status INT NOT NULL, comment LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_29C0DC89AE80F5DF (department_id), INDEX IDX_29C0DC89B03A8386 (created_by_id), INDEX IDX_29C0DC89896DBBDE (updated_by_id), INDEX IDX_29C0DC89F1F495D7 (group_people_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE social_support_person (id INT AUTO_INCREMENT NOT NULL, person_id INT NOT NULL, social_support_group_id INT NOT NULL, start_date DATE NOT NULL, end_date DATE DEFAULT NULL, status INT NOT NULL, comment LONGTEXT DEFAULT NULL COLLATE utf8mb4_unicode_ci, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_4BF2DC553E4CADB5 (social_support_group_id), INDEX IDX_4BF2DC55217BBB47 (person_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE social_support_group ADD CONSTRAINT FK_29C0DC89896DBBDE FOREIGN KEY (updated_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE social_support_group ADD CONSTRAINT FK_29C0DC89AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id)');
        $this->addSql('ALTER TABLE social_support_group ADD CONSTRAINT FK_29C0DC89B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE social_support_group ADD CONSTRAINT FK_29C0DC89F1F495D7 FOREIGN KEY (group_people_id) REFERENCES group_people (id)');
        $this->addSql('ALTER TABLE social_support_person ADD CONSTRAINT FK_4BF2DC55217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE social_support_person ADD CONSTRAINT FK_4BF2DC553E4CADB5 FOREIGN KEY (social_support_group_id) REFERENCES social_support_group (id)');
        $this->addSql('DROP TABLE social_support_grp');
        $this->addSql('DROP TABLE social_support_pers');
    }
}
