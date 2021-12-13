<?php

namespace App\Command\Support;

use App\Service\DoctrineTrait;
use App\Entity\People\RolePerson;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use App\Repository\Support\SupportGroupRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Commande pour mettre à jour le nombre de personnes par suivi (TEMPORAIRE, A SUPPRIMER).
 */
class UpdateFamilyTypologyOfSupportCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:support:update_family_typology';
    protected static $defaultDescription = 'Update family typology and number of people in support';

    protected $supportGroupRepo;
    protected $em;
    protected $stopwatch;

    public function __construct(SupportGroupRepository $supportGroupRepo, EntityManagerInterface $em, Stopwatch $stopwatch)
    {
        $this->supportGroupRepo = $supportGroupRepo;
        $this->em = $em;
        $this->stopwatch = $stopwatch;
        $this->disableListeners($this->em);

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('fix', InputArgument::OPTIONAL, 'Fix the problem')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Query limit', 1000)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = $input->getOption('limit');
        $arg = $input->getArgument('fix');

        $this->stopwatch->start('command');

        $supports = $this->supportGroupRepo->findBy([], ['updatedAt' => 'DESC'], $limit);
        $nbSupports = count($supports);
        $count = 0;

        $io->createProgressBar();
        $io->progressStart($nbSupports);

        foreach ($supports as $supportGroup) {
            $peopleGroup = $supportGroup->getPeopleGroup();
            $nbSupportPeople = 0;

            foreach ($supportGroup->getSupportPeople() as $supportPerson) {
                $person = $supportPerson->getPerson();

                if ($supportGroup->getEndDate()
                    || (null === $supportGroup->getEndDate() && null === $supportPerson->getEndDate())) {
                    ++$nbSupportPeople;
                }
                if (in_array($peopleGroup->getFamilyTypology(), [1, 2]) && 1 === $supportGroup->getNbPeople()
                    && 5 != $supportPerson->getRole()) {
                    $supportPerson->setRole(5);
                    ++$count;
                }
                if (in_array($peopleGroup->getFamilyTypology(), [4, 5]) && $supportGroup->getNbPeople() > 1
                    && true === $supportPerson->getHead() && 4 != $supportPerson->getRole()) {
                    $supportPerson->setRole(4);
                    ++$count;
                }
                if ($person->getAge() <= 16 && RolePerson::ROLE_CHILD != $supportPerson->getRole()) {
                    $supportPerson->setRole(RolePerson::ROLE_CHILD);
                    ++$count;
                }
            }
            if ($supportGroup->getNbPeople() != $nbSupportPeople) {
                $supportGroup->setNbPeople($nbSupportPeople);
                ++$count;
            }
            
            $io->progressAdvance();
        }


        if ('fix' === $arg) {
            $this->em->flush();
        }

        $io->progressFinish();

        $io->success('The typology family of supports are update !'
            ."\n  ".$count.' / '.$nbSupports
            ."\n  ".number_format($this->stopwatch->start('command')->getDuration(), 0, ',', ' ').' ms');

        return Command::SUCCESS;
    }
}
