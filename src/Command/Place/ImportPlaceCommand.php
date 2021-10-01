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

/**
 * Commande pour importer des groupes de places.
 */
class ImportPlaceCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:place:import';

    protected $manager;
    protected $serviceRepo;
    protected $importPlaceDatas;

    public function __construct(
        EntityManagerInterface $manager,
        ServiceRepository $serviceRepo,
        ImportPlaceDatas $importPlaceDatas
    ) {
        $this->manager = $manager;
        $this->serviceRepo = $serviceRepo;
        $this->importPlaceDatas = $importPlaceDatas;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Import places.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
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

        $message = '[OK] '.count($places)." places are imported !\n  ";
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return Command::SUCCESS;
    }
}
