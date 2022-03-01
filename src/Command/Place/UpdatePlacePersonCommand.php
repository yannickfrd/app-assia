<?php

namespace App\Command\Place;

use App\Repository\Support\PlaceGroupRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande pour mettre à jour les prises en charges individuelles.
 */
class UpdatePlacePersonCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:placePerson:update_supportPerson';
    protected static $defaultDescription = 'Update the supportPerson item in the AccommpdationPerson entities.';

    protected $placeGroupRepo;
    protected $em;

    public function __construct(PlaceGroupRepository $placeGroupRepo, EntityManagerInterface $em)
    {
        $this->placeGroupRepo = $placeGroupRepo;
        $this->em = $em;
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

        $placeGroups = $this->placeGroupRepo->findBy([], ['updatedAt' => 'DESC'], $limit);
        $nbPlaceGroups = count($placeGroups);
        $countUpdate = 0;

        $io->createProgressBar();
        $io->progressStart($nbPlaceGroups);

        foreach ($placeGroups as $placeGroup) {
            $supportGroup = $placeGroup->getSupportGroup();

            foreach ($placeGroup->getPlacePeople() as $placePerson) {
                foreach ($supportGroup->getSupportPeople() as $supportPerson) {
                    if (null === $placePerson->getSupportPerson() && $placePerson->getPerson()->getId() === $supportPerson->getPerson()->getId()) {
                        $placePerson->setSupportPerson($supportPerson);
                        ++$countUpdate;
                    }
                }
            }

            $io->progressAdvance();
        }
        
        if ('fix' === $arg) {
            $this->em->flush();
        }
        
        $io->progressFinish();

        $io->success("Update PlacePerson entities is successful !\n  ".$countUpdate.' / '.$nbPlaceGroups);

        return Command::SUCCESS;
    }
}
