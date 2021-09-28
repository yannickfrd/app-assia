<?php

namespace App\Command\Service;

use App\Command\CommandTrait;
use App\Entity\Organization\Service;
use App\Entity\Organization\ServiceDevice;
use App\Repository\Organization\DeviceRepository;
use App\Repository\Organization\PoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Commande pour créer un nouveau service.
 */
class CreateServiceCommand extends Command
{
    use CommandTrait;

    protected static $defaultName = 'app:service:create';

    protected $manager;
    protected $poleRepo;
    protected $deviceRepo;

    public function __construct(
        EntityManagerInterface $manager,
        PoleRepository $poleRepo,
        DeviceRepository $deviceRepo
    ) {
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

        $nameQuestion = new Question("<info>Name</info>:\n> ");
        $name = $helper->ask($input, $output, $nameQuestion);

        $emailQuestion = new Question("<info>Email</info>:\n> ");
        $email = $helper->ask($input, $output, $emailQuestion);

        $phoneQuestion = new Question("<info>Phone</info>:\n> ");
        $phone = $helper->ask($input, $output, $phoneQuestion);

        $poleChoices = [];

        foreach ($this->poleRepo->findAll() as $pole) {
            $poleChoices[$pole->getId()] = $pole->getName();
        }

        $poleQuestion = (new ChoiceQuestion(
            '<info>Pole</info>:',
            $poleChoices,
        ));

        $pole = $helper->ask($input, $output, $poleQuestion);

        $deviceChoices = [];

        foreach ($this->deviceRepo->findAll() as $device) {
            $deviceChoices[$device->getId()] = $device->getName();
        }

        $devicesQuestion = (new ChoiceQuestion(
            '<info>Devices</info>:',
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

        $this->writeMessage('success', "The service {$service->getName()} is create !");

        return Command::SUCCESS;
    }
}
