<?php

namespace App\Command\User;

use App\Repository\Organization\ServiceRepository;
use App\Service\Import\ImportUserDatas;
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
    name: 'app:user:import',
    description: 'Import users from csv file.',
)]
class ImportUserCommand extends Command
{
    protected $em;
    protected $serviceRepo;
    protected $importUserDatas;

    public function __construct(
        EntityManagerInterface $em,
        ServiceRepository $serviceRepo,
        ImportUserDatas $importUserDatas
    ) {
        $this->em = $em;
        $this->serviceRepo = $serviceRepo;
        $this->importUserDatas = $importUserDatas;

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
            '<info>Choice the service(s)</info>:',
            $serviceChoices,
        ))->setMultiselect(true);

        $nameServices = $helper->ask($input, $output, $serviceQuestion);

        $services = new ArrayCollection();
        foreach ($nameServices as $name) {
            $services->add($this->serviceRepo->findOneBy(['name' => $name]));
        }

        $users = $this->importUserDatas->importInDatabase(
            \dirname(__DIR__).'/../../public/import/datas/import_users.csv',
            $services
        );

        $io->success(count($users).' users are imported !');

        return Command::SUCCESS;
    }
}
