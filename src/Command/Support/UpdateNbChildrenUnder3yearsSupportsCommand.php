<?php

namespace App\Command\Support;

use App\Entity\People\RolePerson;
use App\Repository\Support\SupportGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
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

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Query limit', 1000)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = $input->getOption('limit');

        $this->stopwatch->start('command');

        $supports = $this->supportGroupRepo->findBy([], ['updatedAt' => 'DESC'], $limit);

        $today = new \DateTime();

        $count = 0;

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
                    ++$count;
                }
            }
            if ($nbChildren > 0) {
                $supportGroup->setNbChildrenUnder3years($nbChildrenUnder3years);
            }
        }
        $this->em->flush();

        $io->success('The number of children under 3 years in supports are update !'
            ."\n ".$count.' / '.count($supports)
            ."\n  ".number_format($this->stopwatch->start('command')->getDuration(), 0, ',', ' ').' ms');

        return Command::SUCCESS;
    }
}
