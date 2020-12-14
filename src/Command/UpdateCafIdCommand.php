<?php

namespace App\Command;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\People\RolePerson;
use App\Repository\Support\SupportGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour mettre à jour le nombre de personnes par suivi (TEMPORAIRE, A SUPPRIMER).
 */
class UpdateCafIdCommand extends Command
{
    protected static $defaultName = 'app:evaluation:update:cafId';

    protected $repo;
    protected $manager;

    public function __construct(SupportGroupRepository $repo, EntityManagerInterface $manager)
    {
        $this->repo = $repo;
        $this->manager = $manager;

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $message = $this->updateCafId();
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return 0;
    }

    /**
     * Met à jour le numéro de CAF dans le.
     */
    protected function updateCafId()
    {
        $count = 0;
        $nbEvaluations = 0;
        $supports = $this->repo->findAll();

        foreach ($supports as $support) {
            /** @var EvaluationGroup $evaluationGroup */
            $evaluationGroup = $support->getEvaluationsGroup()->first();
            if ($evaluationGroup) {
                ++$nbEvaluations;
                $cafId = $evaluationGroup->getEvalFamilyGroup() ? $evaluationGroup->getEvalFamilyGroup()->getCafId() : null;
                if ($cafId) {
                    foreach ($support->getSupportPeople() as $supportPerson) {
                        /** @var EvaluationPerson $evaluationPerson */
                        $evaluationPerson = $supportPerson->getEvaluationsPerson()->first();
                        if (RolePerson::ROLE_CHILD != $evaluationPerson->getSupportPerson()->getRole()) {
                            if ($evaluationPerson->getEvalBudgetPerson()) {
                                $evaluationPerson->getEvalBudgetPerson()->setCafId($cafId);
                            }
                            ++$count;
                        }
                    }
                }
            }
        }

        $this->manager->flush();

        return "[OK] The CafId is update !\n  ".$count.' / '.$nbEvaluations;
    }
}
