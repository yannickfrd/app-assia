<?php

namespace App\Command\User;

use App\Command\CommandTrait;
use App\Entity\Organization\ServiceUser;
use App\Entity\Organization\User;
use App\Form\Utils\Choices;
use App\Notification\UserNotification;
use App\Repository\Organization\ServiceRepository;
use App\Repository\Organization\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Commande pour créer un nouvel utilisateur.
 */
class CreateUserCommand extends Command
{
    use CommandTrait;

    protected static $defaultName = 'app:user:create';

    protected $manager;
    protected $userRepo;
    protected $serviceRepo;
    protected $encoder;
    protected $slugger;
    protected $userNotification;

    public function __construct(
        EntityManagerInterface $manager,
        UserRepository $userRepo,
        ServiceRepository $serviceRepo,
        UserPasswordEncoderInterface $encoder,
        SluggerInterface $slugger,
        UserNotification $userNotification
    ) {
        $this->manager = $manager;
        $this->userRepo = $userRepo;
        $this->serviceRepo = $serviceRepo;
        $this->encoder = $encoder;
        $this->slugger = $slugger;
        $this->userNotification = $userNotification;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Create a new user.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $lastnameQuestion = new Question("<info>Lastname</info>:\n> ");
        $lastname = $helper->ask($input, $output, $lastnameQuestion);

        $firstnameQuestion = new Question("<info>Firstname</info>:\n> ");
        $firstname = $helper->ask($input, $output, $firstnameQuestion);

        $emailQuestion = new Question("<info>Email</info>:\n> ");
        $email = $helper->ask($input, $output, $emailQuestion);

        $phoneQuestion = new Question("<info>Phone</info>:\n> ");
        $phone = $helper->ask($input, $output, $phoneQuestion);

        $statusQuestion = (new ChoiceQuestion(
            '<info>Status</info> [default: 1 Travailleur social]:',
            User::STATUS,
            1
        ))->setErrorMessage('Status %s is invalid.');

        $status = $helper->ask($input, $output, $statusQuestion);

        $roleQuestion = (new ChoiceQuestion(
            '<info>Role</info> [default: ROLE_USER]:',
            [
                0 => 'ROLE_USER',
                1 => 'ROLE_ADMIN',
                2 => 'ROLE_SUPER_ADMIN',
            ],
            0
        ))->setErrorMessage('Role %s is invalid.');

        $role = $helper->ask($input, $output, $roleQuestion);

        $passwordQuestion = (new Question("<info>Password</info>\n> ", bin2hex(random_bytes(8))))
            ->setHidden(true)
            ->setHiddenFallback(false);
        $password = $helper->ask($input, $output, $passwordQuestion);

        $serviceChoices = [];

        foreach ($this->serviceRepo->findAll() as $service) {
            $serviceChoices[$service->getId()] = $service->getName();
        }

        $servicesQuestion = (new ChoiceQuestion(
            'Service',
            $serviceChoices,
        ))->setMultiselect(true);

        $services = $helper->ask($input, $output, $servicesQuestion);

        $user = new User();

        $user
            ->setFirstName($firstname)
            ->setLastName($lastname)
            ->setUsername($this->getUsername($firstname, $lastname))
            ->setPassword($this->encoder->encodePassword($user, $password))
            ->setStatus(Choices::getchoices(User::STATUS)[$status])
            ->setRoles([$role])
            ->setEmail($email)
            ->setphone1($phone);

        $this->manager->persist($user);

        if ($this->userExists($user)) {
            $this->writeMessage('error', 'This user already exists !');

            return Command::FAILURE;
        }

        foreach ($services as $service) {
            $serviceUser = (new ServiceUser())
                ->setUser($user)
                ->setService($this->serviceRepo->findOneBy(['name' => $service]));

            $this->manager->persist($serviceUser);
        }

        $this->manager->flush();

        $this->writeMessage('success', "The user {$user->getLastname()} {$user->getFirstname()} is create !");

        $notificationChoices = [
            Choices::YES => 'Yes',
            Choices::NO => 'No',
        ];

        $notificationQuestion = (new ChoiceQuestion(
            'Email notification ? [default: Yes]',
            $notificationChoices,
            Choices::YES
        ))->setErrorMessage('Response %s is invalid.');

        $notification = $helper->ask($input, $output, $notificationQuestion);

        if (Choices::YES === Choices::getchoices($notificationChoices)[$notification]) {
            $this->userNotification->newUser($user);

            $this->writeMessage('success', "The email notification is sended !\n  ");
        }

        return Command::SUCCESS;
    }

    /**
     * Formatte et donne le login de l'utilisateur.
     */
    protected function getUsername(string $firstname, string $lastname)
    {
        $nameArray = explode('-', $firstname);
        $username = '';
        foreach ($nameArray as $value) {
            $username = $username.substr($value, 0, 1);
        }

        return strtolower($this->slugger->slug($username).'.'.$this->slugger->slug($lastname));
    }

    /**
     * Vérifie si l'utilisateur existe déjà dans la base de données.
     */
    protected function userExists(User $user)
    {
        return $this->userRepo->findOneBy([
            'username' => $user->getUsername(),
        ]);
    }
}
