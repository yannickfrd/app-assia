<?php

namespace App\Command;

use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Repository\Evaluation\EvaluationPersonRepository;

/**
 * Corrige le support_person_id de evaluation_person des évaluations duppliquées (TEMPORAIRE, A SUPPRIMER).
 */
class UpdateDupplicatedEvaluationsCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:evaluation:update:support_person_id';

    protected $repo;
    protected $manager;

    public function __construct(EvaluationPersonRepository $repo, EntityManagerInterface $manager)
    {
        $this->repo = $repo;
        $this->manager = $manager;
        $this->disableListeners($this->manager);
        $this->manager->getFilters()->disable('softdeleteable');

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $this->updateEvaluations();
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return 0;
    }

    protected function updateEvaluations()
    {
        $evaluationPeople = $this->repo->findAll();
        $count = 0;
        foreach ($this->repo->findAll() as $evaluationPerson) {
            foreach ($evaluationPerson->getEvaluationGroup()->getSupportGroup()->getSupportPeople() as $supportPerson) {
                if ($supportPerson->getPerson()->getId() === $evaluationPerson->getSupportPerson()->getPerson()->getId()
                    && $supportPerson->getId() !== $evaluationPerson->getSupportPerson()->getId()) {
                    $evaluationPerson->setSupportPerson($supportPerson);
                    ++$count;
                }
            }
        }

        $this->manager->flush();

        return "[OK] The evaluation people are update ! \n ".$count.' / '.count($evaluationPeople);
    }
}
