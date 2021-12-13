<?php

namespace App\Command\Support;

use App\Entity\People\RolePerson;
use App\Repository\Support\SupportGroupRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Commande pour mettre à jour le nombre d'enfants de moins de 3 ans.
 */
class UpdateNbChildrenUnder3yearsSupportsCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:support:update_nb_children_under_3_years';
    protected static $defaultDescription = 'Update the number of children under 3 years in supports';

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

        $today = new \DateTime();
        $supports = $this->supportGroupRepo->findBy([], ['updatedAt' => 'DESC'], $limit);
        $nbSupports = count($supports);

        $count = 0;

        $io->createProgressBar();
        $io->progressStart($nbSupports);

        foreach ($supports as $supportGroup) {
            $nbChildren = 0;
            $nbChildrenUnder3years = 0;
            foreach ($supportGroup->getSupportPeople() as $supportPerson) {
                if (RolePerson::ROLE_CHILD === $supportPerson->getRole()) {
                    ++$nbChildren;
                }
                $birthdate = $supportPerson->getPerson()->getBirthdate();
                $age = $birthdate->diff($supportPerson->getEndDate() ?? $today)->y ?? 0;
                if ($age < 3) {
                    ++$nbChildrenUnder3years;
                }
            }
            if ($nbChildren > 0 && $supportGroup->getNbChildrenUnder3years() !== $nbChildrenUnder3years) {
                $supportGroup->setNbChildrenUnder3years($nbChildrenUnder3years);
                ++$count;
            }

            $io->progressAdvance();
        }

        if ('fix' === $arg) {
            $this->em->flush();
        }

        $io->progressFinish();

        $io->success('The number of children under 3 years in supports are update !'
            ."\n  ".$count.' / '.$nbSupports
            ."\n  ".number_format($this->stopwatch->start('command')->getDuration(), 0, ',', ' ').' ms');

        return Command::SUCCESS;
    }
}
