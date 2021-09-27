<?php

namespace App\Command;

use App\Service\DoctrineTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use App\Repository\Organization\ServiceRepository;
use App\Service\Import\ImportUserDatas;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Commande pour importer des utilisateurs.
 */
class ImportUserCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:user:import';

    protected $manager;
    protected $serviceRepo;
    protected $importUserDatas;

    public function __construct(
        EntityManagerInterface $manager,
        ServiceRepository $serviceRepo,
        ImportUserDatas $importUserDatas
    ){
        $this->manager = $manager;
        $this->serviceRepo = $serviceRepo;
        $this->importUserDatas = $importUserDatas;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Import users.');
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
        ));

        $service = $helper->ask($input, $output, $serviceQuestion);
        
        $users = $this->importUserDatas->importInDatabase(
            \dirname(__DIR__).'/../public/import/datas/import_users.csv', 
            $this->serviceRepo->findOneBy(['name' => $service])
        );

        $message = "[OK] ".count($users)." users are imported !\n  ";
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return Command::SUCCESS;
    }
}
