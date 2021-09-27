<?php

namespace App\Command;

use App\Service\DoctrineTrait;
use App\Entity\Organization\Service;
use App\Entity\Organization\ServiceDevice;
use App\Repository\Organization\DeviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use App\Repository\Organization\PoleRepository;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Commande pour créer un nouveau service.
 */
class CreateServiceCommand extends Command
{
    use DoctrineTrait;

    protected static $defaultName = 'app:service:create';

    protected $manager;
    protected $poleRepo;
    protected $deviceRepo;

    public function __construct(
        EntityManagerInterface $manager,
        PoleRepository $poleRepo,
        DeviceRepository $deviceRepo
    ){
        $this->manager = $manager;
        $this->poleRepo = $poleRepo;
        $this->deviceRepo = $deviceRepo;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Create a new service.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $nameQuestion = new Question("Name ? \n");
        $name = $helper->ask($input, $output, $nameQuestion);

        $emailQuestion = new Question("Email ? \n");
        $email = $helper->ask($input, $output, $emailQuestion);

        $phoneQuestion = new Question("Phone ? \n");
        $phone = $helper->ask($input, $output, $phoneQuestion);

        $poleChoices = [];

        foreach ($this->poleRepo->findAll() as $pole) {
            $poleChoices[$pole->getId()] = $pole->getName();
        }

        $poleQuestion = (new ChoiceQuestion(
            'Pole',
            $poleChoices,
        ));

        $pole = $helper->ask($input, $output, $poleQuestion);

        $deviceChoices = [];

        foreach ($this->deviceRepo->findAll() as $device) {
            $deviceChoices[$device->getId()] = $device->getName();
        }

        $devicesQuestion = (new ChoiceQuestion(
            'Devices',
            $deviceChoices,
        ))->setMultiselect(true);

        $devices = $helper->ask($input, $output, $devicesQuestion);

        $service = (new Service())
            ->setName($name)
            ->setPole($this->poleRepo->findOneBy(['name' => $pole]))
            ->setPhone1($phone)
            ->setEmail($email);

        $this->manager->persist($service);

        foreach ($devices as $device) {
            $serviceDevice = (new ServiceDevice())
                ->setDevice($this->deviceRepo->findOneBy(['name' => $device]))
                ->setService($service);
    
            $this->manager->persist($serviceDevice);
        }            

        $this->manager->flush();

        $message = "[OK] The service {$service->getName()} is create !\n  ";
        $output->writeln("\e[30m\e[42m\n ".$message."\e[0m\n");

        return Command::SUCCESS;
    }
}
