<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220724122413 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE support_group ADD eval_init_group_id INT DEFAULT NULL, ADD origin_request_id INT DEFAULT NULL, ADD avdl_id INT DEFAULT NULL, ADD hotel_support_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE support_group ADD CONSTRAINT FK_9F7A521DEC74FA8D FOREIGN KEY (eval_init_group_id) REFERENCES eval_init_group (id)');
        $this->addSql('ALTER TABLE support_group ADD CONSTRAINT FK_9F7A521DFBA5E2E8 FOREIGN KEY (origin_request_id) REFERENCES origin_request (id)');
        $this->addSql('ALTER TABLE support_group ADD CONSTRAINT FK_9F7A521DE994F3E7 FOREIGN KEY (avdl_id) REFERENCES avdl (id)');
        $this->addSql('ALTER TABLE support_group ADD CONSTRAINT FK_9F7A521D76C15EE8 FOREIGN KEY (hotel_support_id) REFERENCES hotel_support (id)');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_9F7A521DEC74FA8D ON support_group (eval_init_group_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9F7A521DFBA5E2E8 ON support_group (origin_request_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9F7A521DE994F3E7 ON support_group (avdl_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9F7A521D76C15EE8 ON support_group (hotel_support_id)');

        $this->addSql('ALTER TABLE support_person ADD eval_init_person_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE support_person ADD CONSTRAINT FK_BC2631865AD8B0CA FOREIGN KEY (eval_init_person_id) REFERENCES eval_init_person (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BC2631865AD8B0CA ON support_person (eval_init_person_id)');


        $this->addSql('UPDATE support_group LEFT JOIN eval_init_group  
            ON support_group.id = eval_init_group.support_group_id 
            SET support_group.eval_init_group_id = eval_init_group.id
        ');
        $this->addSql('UPDATE support_group LEFT JOIN origin_request  
            ON support_group.id = origin_request.support_group_id 
            SET support_group.origin_request_id = origin_request.id
        ');
        $this->addSql('UPDATE support_group LEFT JOIN avdl  
            ON support_group.id = avdl.support_group_id 
            SET support_group.avdl_id = avdl.id
        ');
        $this->addSql('UPDATE support_group LEFT JOIN hotel_support  
            ON support_group.id = hotel_support.support_group_id 
            SET support_group.hotel_support_id = hotel_support.id
        ');

        $this->addSql('ALTER TABLE avdl DROP FOREIGN KEY FK_B737D8484AE25278');
        $this->addSql('DROP INDEX UNIQ_B737D8484AE25278 ON avdl');
        $this->addSql('ALTER TABLE avdl DROP support_group_id');

        $this->addSql('ALTER TABLE eval_init_group DROP FOREIGN KEY FK_53AF32384AE25278');
        $this->addSql('DROP INDEX UNIQ_995638124AE25278 ON eval_init_group');
        $this->addSql('ALTER TABLE eval_init_group DROP support_group_id');

        $this->addSql('ALTER TABLE hotel_support DROP FOREIGN KEY FK_534EF58C4AE25278');
        $this->addSql('DROP INDEX UNIQ_534EF58C4AE25278 ON hotel_support');
        $this->addSql('ALTER TABLE hotel_support DROP support_group_id');

        $this->addSql('ALTER TABLE origin_request DROP FOREIGN KEY FK_8EE09AF24AE25278');
        $this->addSql('DROP INDEX UNIQ_8EE09AF24AE25278 ON origin_request');
        $this->addSql('ALTER TABLE origin_request DROP support_group_id');
    
        $this->addSql('ALTER TABLE eval_init_person DROP FOREIGN KEY FK_F7EE30A197A920F1');
        $this->addSql('DROP INDEX UNIQ_2C9F007D97A920F1 ON eval_init_person');
        $this->addSql('ALTER TABLE eval_init_person DROP support_person_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE avdl ADD support_group_id INT NOT NULL');
        $this->addSql('ALTER TABLE avdl ADD CONSTRAINT FK_B737D8484AE25278 FOREIGN KEY (support_group_id) REFERENCES support_group (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B737D8484AE25278 ON avdl (support_group_id)');
        $this->addSql('ALTER TABLE eval_init_group ADD support_group_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_init_group ADD CONSTRAINT FK_53AF32384AE25278 FOREIGN KEY (support_group_id) REFERENCES support_group (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_995638124AE25278 ON eval_init_group (support_group_id)');
        $this->addSql('ALTER TABLE hotel_support ADD support_group_id INT NOT NULL');
        $this->addSql('ALTER TABLE hotel_support ADD CONSTRAINT FK_534EF58C4AE25278 FOREIGN KEY (support_group_id) REFERENCES support_group (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_534EF58C4AE25278 ON hotel_support (support_group_id)');
        $this->addSql('ALTER TABLE origin_request ADD support_group_id INT NOT NULL');
        $this->addSql('ALTER TABLE origin_request ADD CONSTRAINT FK_8EE09AF24AE25278 FOREIGN KEY (support_group_id) REFERENCES support_group (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8EE09AF24AE25278 ON origin_request (support_group_id)');
        $this->addSql('ALTER TABLE support_group DROP FOREIGN KEY FK_9F7A521DEC74FA8D');
        $this->addSql('ALTER TABLE support_group DROP FOREIGN KEY FK_9F7A521DFBA5E2E8');
        $this->addSql('ALTER TABLE support_group DROP FOREIGN KEY FK_9F7A521DE994F3E7');
        $this->addSql('ALTER TABLE support_group DROP FOREIGN KEY FK_9F7A521D76C15EE8');
        $this->addSql('DROP INDEX UNIQ_9F7A521DEC74FA8D ON support_group');
        $this->addSql('DROP INDEX UNIQ_9F7A521DFBA5E2E8 ON support_group');
        $this->addSql('DROP INDEX UNIQ_9F7A521DE994F3E7 ON support_group');
        $this->addSql('DROP INDEX UNIQ_9F7A521D76C15EE8 ON support_group');
        $this->addSql('ALTER TABLE support_group DROP eval_init_group_id, DROP origin_request_id, DROP avdl_id, DROP hotel_support_id');
    
        $this->addSql('ALTER TABLE eval_init_person ADD support_person_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE eval_init_person ADD CONSTRAINT FK_F7EE30A197A920F1 FOREIGN KEY (support_person_id) REFERENCES support_person (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_2C9F007D97A920F1 ON eval_init_person (support_person_id)');
        $this->addSql('ALTER TABLE hotel_support DROP priority_criteria');
        $this->addSql('ALTER TABLE support_person DROP FOREIGN KEY FK_BC2631865AD8B0CA');
        $this->addSql('DROP INDEX UNIQ_BC2631865AD8B0CA ON support_person');
        $this->addSql('ALTER TABLE support_person DROP eval_init_person_id');
    }
}
