<?php

namespace App\Command\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class AnonymizeDatabaseCommand extends Command
{
    protected static $defaultName = 'app:database:anonymize';
    protected static $defaultDescription = 'Anonymize all the database.';

    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();

        $this->em = $em;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ('dev' != $_SERVER['APP_ENV'] || 'localhost' != $_SERVER['DB_HOST']) {
            $io->error('Invalid environnement!');

            return Command::FAILURE;
        }

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $confirmationQuestion = new Question(
            '<info>Are you sure to anonymize the datatase? (yes/no)</info> [<comment>no</comment>]'.PHP_EOL.'> ', false);
        $answer = $helper->ask($input, $output, $confirmationQuestion);

        if (false === (bool) preg_match('/^y/i', $answer)) {
            return Command::FAILURE;
        }

        $statement = $this->em->getConnection()->prepare($this->getSQL());
        $statement->executeQuery();

        $io->success('The database is anonymized!');

        return Command::SUCCESS;
    }

    protected function getSQL(): string
    {
        return 'UPDATE user SET lastname = CONCAT("XXX", id), firstname = "Xxx";
            UPDATE person SET lastname = CONCAT("XXX", id), firstname = "Xxx";
            UPDATE person SET comment = "XXX" WHERE comment != "";
            UPDATE person SET email = "xxx@xxx.xx" WHERE email != "";
            UPDATE person SET phone1 = "0102030405" WHERE phone1 != "";
            UPDATE person SET phone2 = "0102030405" WHERE phone2 != "";
            UPDATE person SET email = "xxx@xxx.xx" WHERE email != "";
            UPDATE person SET contact_other_person = "" WHERE contact_other_person != "";

            UPDATE people_group SET comment = "XXX" WHERE comment != "";

            UPDATE support_group SET comment = "XXX" WHERE comment != "";
            UPDATE support_group SET end_status_comment = "XXX" WHERE end_status_comment != "";

            UPDATE avdl SET support_comment = "XXX" WHERE support_comment != "";
            UPDATE avdl SET diag_comment = "XXX" WHERE diag_comment != "";
            UPDATE avdl SET end_support_comment = "XXX" WHERE end_support_comment != "";

            UPDATE hotel_support SET rosalie_id = "XXX" WHERE rosalie_id != "";
            UPDATE hotel_support SET end_support_comment = "XXX" WHERE end_support_comment != "";
            UPDATE hotel_support SET emergency_action_precision = "XXX" WHERE emergency_action_precision != "";

            UPDATE place_group SET comment = "XXX" WHERE comment != "";
            UPDATE place_group SET comment_end_reason = "XXX" WHERE comment_end_reason != "";
            UPDATE place_person SET comment_end_reason = "XXX" WHERE comment_end_reason != "";

            UPDATE evaluation_group SET background_people = "XXX", conclusion = "XXX" WHERE background_people != "" OR conclusion != "";
            UPDATE eval_family_group SET comment_eval_family_group = "XXX" WHERE comment_eval_family_group != "";
            UPDATE eval_social_group SET comment_eval_social_group = "XXX" WHERE comment_eval_social_group != "";
            UPDATE eval_budget_group SET caf_id = "XXX" WHERE caf_id != "";
            UPDATE eval_housing_group SET comment_eval_housing = "XXX" WHERE comment_eval_housing != "";
            UPDATE eval_housing_group SET social_housing_request_id = "XXX" WHERE social_housing_request_id != "";
            UPDATE eval_housing_group SET syplo_id = "XXX" WHERE syplo_id != "";
            UPDATE eval_housing_group SET dalo_id = "XXX" WHERE dalo_id != "";
            UPDATE eval_housing_group SET housing_expe_comment = "XXX" WHERE housing_expe_comment != "";
            UPDATE eval_housing_group SET domiciliation_address = "XXX" WHERE domiciliation_address != "";
            UPDATE eval_housing_group SET domiciliation_comment = "XXX" WHERE domiciliation_comment != "";
            UPDATE eval_housing_group SET hsg_action_record_id = "XXX" WHERE hsg_action_record_id != "";

            UPDATE eval_hotel_life_group SET food = "XXX" WHERE food != "";
            UPDATE eval_hotel_life_group SET clothing = "XXX" WHERE clothing != "";
            UPDATE eval_hotel_life_group SET room_maintenance = "XXX" WHERE room_maintenance != "";
            UPDATE eval_hotel_life_group SET other_hotel_life = "XXX" WHERE other_hotel_life != "";
            UPDATE eval_hotel_life_group SET comment_hotel_life = "XXX" WHERE comment_hotel_life != "";
            UPDATE eval_justice_person SET comment_eval_justice = "XXX" WHERE comment_eval_justice != "";
            UPDATE eval_family_person SET comment_eval_family_person = "XXX" WHERE comment_eval_family_person != "";
            UPDATE eval_family_person SET childcare_school_location = "XXX" WHERE childcare_school_location != "";
            UPDATE eval_social_person SET comment_eval_social_person = "XXX" WHERE comment_eval_social_person != "";
            UPDATE eval_social_person SET social_security_office = "XXX" WHERE social_security_office != "";
            UPDATE eval_adm_person SET country = "XXX" WHERE country != "";
            UPDATE eval_adm_person SET agdref_id = "XXX" WHERE agdref_id != "";
            UPDATE eval_adm_person SET ofpra_registration_id = "XXX" WHERE ofpra_registration_id != "";
            UPDATE eval_adm_person SET cnda_id = "XXX" WHERE cnda_id != "";
            UPDATE eval_adm_person SET comment_eval_adm_person = "XXX" WHERE comment_eval_adm_person != "";
            UPDATE eval_prof_person SET job_center_id = "XXX" WHERE job_center_id != "";
            UPDATE eval_prof_person SET comment_eval_prof = "XXX" WHERE comment_eval_prof != "";
            UPDATE eval_budget_person SET comment_eval_budget = "XXX" WHERE comment_eval_budget != "";

            UPDATE rdv r
            LEFT JOIN support_group sg ON sg.id = r.support_group_id
            LEFT JOIN support_person sp ON sg.id = sp.support_group_id
            LEFT JOIN person p ON p.id = sp.person_id
            SET r.title = CONCAT("RDV ", p.lastname, " ", p.firstname)
            WHERE sp.head IS TRUE;
            UPDATE rdv SET content = "XXX" WHERE content != "";

            UPDATE task t
            LEFT JOIN support_group sg ON sg.id = t.support_group_id
            LEFT JOIN support_person sp ON sg.id = sp.support_group_id
            LEFT JOIN person p ON p.id = sp.person_id
            SET t.title = CONCAT("Tâche ", p.lastname, " ", p.firstname)
            WHERE sp.head IS TRUE;
            UPDATE task SET content = "XXX" WHERE content != "";

            UPDATE note n
            LEFT JOIN support_group sg  ON sg.id = n.support_group_id
            LEFT JOIN support_person sp ON sg.id = sp.support_group_id
            LEFT JOIN person p ON p.id = sp.person_id
            SET n.title = CONCAT("Note ", p.lastname, " ", p.firstname), n.content = "YYY"
            WHERE sp.head IS TRUE;

            UPDATE document SET name = CONCAT("Document ", document.id);
            UPDATE document SET content = "XXX" WHERE content != "";

            UPDATE payment SET comment = "XXX", comment_export = "XXX" WHERE comment != "" OR comment_export != "";

            UPDATE referent SET name = "XXX", social_worker = "XXX";
            UPDATE referent SET social_worker2 = "XXX", social_worker2 = "XXX" WHERE social_worker2 != "";
            UPDATE referent SET email1 = "xxx@xxx.xx" WHERE email1 != "";
            UPDATE referent SET email2 = "xxx@xxx.xx" WHERE email2 != "";
            UPDATE referent SET phone1 = "0102030405" WHERE phone1 != "";
            UPDATE referent SET phone2 = "0102030405" WHERE phone2 != "";
            UPDATE referent SET comment = "XXX" WHERE comment != "";
        ';
    }
}
