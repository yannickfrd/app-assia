<?php

namespace App\Command\Place;

use App\Repository\Support\PlaceGroupRepository;
use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
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
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Query limit', 1000)
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $limit = $input->getOption('limit');

        $nbPlacePeople = 0;
        $countUpdate = 0;

        $placeGroups = $this->placeGroupRepo->findBy([], ['updatedAt' => 'DESC'], $limit);

        foreach ($placeGroups as $placeGroup) {
            $supportGroup = $placeGroup->getSupportGroup();

            foreach ($placeGroup->getPlacePeople() as $placePerson) {
                ++$nbPlacePeople;
                foreach ($supportGroup->getSupportPeople() as $supportPerson) {
                    if (null === $placePerson->getSupportPerson() && $placePerson->getPerson()->getId() === $supportPerson->getPerson()->getId()) {
                        $placePerson->setSupportPerson($supportPerson);
                        ++$countUpdate;
                    }
                }
            }
        }
        $this->em->flush();

        $io->success("Update PlacePerson entities is successfull !\n  ".$countUpdate.' / '.$nbPlacePeople);

        return Command::SUCCESS;
    }
}
