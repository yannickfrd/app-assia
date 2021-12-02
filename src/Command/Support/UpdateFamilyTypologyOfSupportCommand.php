<?php

namespace App\Command\Support;

use App\Entity\People\RolePerson;
use App\Repository\Support\SupportGroupRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;

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
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Query limit', 1000)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = $input->getOption('limit');

        $this->stopwatch->start('command');

        $supports = $this->supportGroupRepo->findBy([], ['updatedAt' => 'DESC'], $limit);
        $count = 0;

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
        }

        $this->em->flush();

        $io->success('The typology family of supports are update !'
            ."\n  ".$count.' / '.count($supports)
            ."\n  ".number_format($this->stopwatch->start('command')->getDuration(), 0, ',', ' ').' ms');

        return Command::SUCCESS;
    }
}
