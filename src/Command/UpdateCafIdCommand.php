<?php

namespace App\Command;

use App\Entity\Evaluation\EvalBudgetGroup;
use App\Repository\Support\SupportGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour mettre à jour le numéro CAF (TEMPORAIRE, A SUPPRIMER).
 */
class UpdateCafIdCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:evaluation:update:cafId';

    protected $repo;
    protected $manager;

    public function __construct(SupportGroupRepository $repo, EntityManagerInterface $manager)
    {
        $this->repo = $repo;
        $this->manager = $manager;
        $this->disableListeners();

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $this->updateCafId();
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return 0;
    }

    /**
     * Met à jour le numéro de CAF dans l'évaluaiton budgétaire du group.
     */
    protected function updateCafId()
    {
        $count = 0;
        $nbEvaluations = 0;
        $supports = $this->repo->findAll();

        foreach ($supports as $support) {
            $evaluationGroup = $support->getEvaluationsGroup()[0];
            if ($evaluationGroup) {
                ++$nbEvaluations;
                $cafId = $evaluationGroup->getEvalFamilyGroup() ? $evaluationGroup->getEvalFamilyGroup()->getCafId() : null;
                if ($cafId) {
                    if ($evaluationGroup->getEvalBudgetGroup()) {
                        $evaluationGroup->getEvalBudgetGroup()->setCafId($cafId);
                    } else {
                        (new EvalBudgetGroup())
                            ->setEvaluationGroup($evaluationGroup)
                            ->setCafId($cafId);
                    }
                    ++$count;
                }
            }
        }

        $this->manager->flush();

        return "[OK] The CafId is update !\n  ".$count.' / '.$nbEvaluations;
    }
}