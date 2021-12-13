<?php

namespace App\Command\Evaluation;

use App\Entity\Evaluation\EvaluationGroup;
use App\Service\DoctrineTrait;
use App\Service\Evaluation\EvaluationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Vérifie l'adéquation entre le nombre de personnes dans l'évaluation sociale et dans le suivi.
 */
class CheckEvaluationPeopleCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:evaluation:check_people';
    protected static $defaultDescription = 'Check is the number of people in evaluation is valid.';

    protected $em;
    protected $evaluationManager;
    protected int $count = 0;

    public function __construct(EntityManagerInterface $em, EvaluationManager $evaluationManager)
    {
        $this->em = $em;
        $this->evaluationManager = $evaluationManager;
        $this->disableListeners($this->em);

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('fix', InputArgument::OPTIONAL, 'Fix the problem (add or remove people in evaluation)')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Query limit', 1000)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = $input->getOption('limit');
        $arg = $input->getArgument('fix');

        $evaluations = $this->em->getRepository(EvaluationGroup::class)->findBy([], ['updatedAt' => 'DESC'], $limit);
        $nbEvaluations = count($evaluations);
        $count = 0;

        $io->createProgressBar();
        $io->progressStart($nbEvaluations);

        try {
            foreach ($evaluations as $evaluationGroup) {
                $supportGroup = $evaluationGroup->getSupportGroup();
                $supportPeople = $supportGroup->getSupportPeople();
                $nbSupportPeople = $supportPeople->count();
                $evaluationPeople = $evaluationGroup->getEvaluationPeople();
                $nbEvaluationPeople = $evaluationPeople->count();

                if ($nbEvaluationPeople != $nbSupportPeople) {
                    echo "\n- SupportId {$supportGroup->getId()}: $nbSupportPeople supportPeople vs $nbEvaluationPeople evaluationPeople \n";
                    ++$count;

                    if ('fix' != $arg) {
                        continue;
                    }

                    foreach ($supportPeople as $supportPerson) {
                        if (false === $this->evaluationManager->personIsInEvaluation($supportPerson, $evaluationGroup)) {
                            $this->evaluationManager->createEvaluationPerson($supportPerson, $evaluationGroup);
                            echo "  => Create evaluationPerson with supportPersonId {$supportPerson->getId()}\n";
                        }
                    }
                    foreach ($evaluationPeople as $evaluationPerson) {
                        if (false === $this->evaluationManager->personIsInSupport($evaluationPerson, $supportGroup)) {
                            $this->em->remove($evaluationPerson);
                            echo "  => Delete evaluationPerson with id {$evaluationPerson->getId()}\n";
                        }
                    }
                }
                
                $io->progressAdvance();
            }
        } catch (\Throwable $th) {
            $io->error($th->getMessage());
            exit;
        }

        if ('fix' === $arg) {
            $this->em->flush();
        }

        $io->progressFinish();

        $io->success("The people in evaluation are checked :\n ".$count.' / '.$nbEvaluations.' are invalids');

        return Command::SUCCESS;
    }
}
