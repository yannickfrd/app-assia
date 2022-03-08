<?php

namespace App\Command\Evaluation;

use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Met à jour les informations relatives aux suivis PMI du groupe vers la demandeuse principale. TEMPORAIRE A SUPPRIMER.
 */
class UpdatePmiCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:evaluation:evalFamily:update_pmi';

    protected $evaluationGroupRepo;
    protected $em;

    public function __construct(EvaluationGroupRepository $evaluationGroupRepo, EntityManagerInterface $em)
    {
        $this->evaluationGroupRepo = $evaluationGroupRepo;
        $this->em = $em;
        $this->disableListeners($this->em);

        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $evaluations = $this->evaluationGroupRepo->findAll();
        $nbEvaluations = count($evaluations);
        $count = 0;

        $io->createProgressBar();
        $io->progressStart($nbEvaluations);

        foreach ($evaluations as $evaluationGroup) {
            $evalFamilyGroup = $evaluationGroup->getEvalFamilyGroup();
            if ($evalFamilyGroup && $evalFamilyGroup->getPmiFollowUp()) {
                foreach ($evaluationGroup->getEvaluationPeople() as $evaluationPerson) {
                    $supportPerson = $evaluationPerson->getSupportPerson();
                    if (RolePerson::ROLE_CHILD != $supportPerson->getRole() && Person::GENDER_FEMALE === $supportPerson->getPerson()->getGender()) {
                        $evalFamilyPerson = $evaluationPerson->getEvalFamilyPerson();

                        if ($evalFamilyPerson) {
                            $evalFamilyPerson
                                ->setPmiFollowUp($evalFamilyGroup->getPmiFollowUp())
                                ->setPmiName($evalFamilyGroup->getPmiName());
                            continue;
                        }
                        $io->error('evalFamilyPerson don\'t exist in supportGroup with ID '.$evaluationGroup->getSupportGroup()->getId());
                    }
                }
                ++$count;
            }

            $io->progressAdvance();
        }
        $this->em->flush();

        $io->progressFinish();

        $io->success("The PMI informations are update !\n ".$count.' / '.$nbEvaluations);

        return Command::SUCCESS;
    }
}
