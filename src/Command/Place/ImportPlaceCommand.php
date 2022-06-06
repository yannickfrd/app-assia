<?php

namespace App\Command\Place;

use App\Repository\Organization\ServiceRepository;
use App\Service\DoctrineTrait;
use App\Service\Import\ImportPlaceDatas;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:place:import',
    description: 'Import places from csv file.',
)]
class ImportPlaceCommand extends Command
{
    use DoctrineTrait;

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

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var QuestionHelper $helper */
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
