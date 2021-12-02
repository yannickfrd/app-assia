<?php

namespace App\Command\Place;

use App\Repository\Organization\ServiceRepository;
use App\Service\DoctrineTrait;
use App\Service\Import\ImportPlaceDatas;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Commande pour importer des groupes de places.
 */
class ImportPlaceCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:place:import';

    protected $em;
    protected $serviceRepo;
    protected $importPlaceDatas;

    public function __construct(
        EntityManagerInterface $em,
        ServiceRepository $serviceRepo,
        ImportPlaceDatas $importPlaceDatas
    ) {
        $this->em = $em;
        $this->serviceRepo = $serviceRepo;
        $this->importPlaceDatas = $importPlaceDatas;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Import places.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $helper = $this->getHelper('question');

        $serviceChoices = [];

        foreach ($this->serviceRepo->findAll() as $service) {
            $serviceChoices[$service->getId()] = $service->getName();
        }

        $serviceQuestion = (new ChoiceQuestion(
            'Choice the service : ',
            $serviceChoices,
        ))->setMultiselect(true);

        $nameServices = $helper->ask($input, $output, $serviceQuestion);

        $services = new ArrayCollection();
        foreach ($nameServices as $name) {
            $services->add($this->serviceRepo->findOneBy(['name' => $name]));
        }

        $places = $this->importPlaceDatas->importInDatabase(
            \dirname(__DIR__).'/../../public/import/datas/import_places.csv',
            $services
        );

        $io->success(count($places).' places are imported !');

        return Command::SUCCESS;
    }
}
