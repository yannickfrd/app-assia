<?php

namespace App\Service\Import;

use App\Entity\Service;
use App\Entity\ServiceUser;
use App\Entity\User;
use App\Notification\MailNotification;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImportDatasUser extends ImportDatas
{
    protected $manager;
    protected $notification;
    protected $slugger;

    protected $fields;
    protected $field;

    protected $repoUser;
    protected $passwordEncoder;

    protected $items = [];
    protected $existUsers = [];

    public function __construct(
        EntityManagerInterface $manager,
        MailNotification $notification,
        UserRepository $repoUser,
        UserPasswordEncoderInterface $passwordEncoder,
        SluggerInterface $slugger)
    {
        $this->manager = $manager;
        $this->notification = $notification;
        $this->repoUser = $repoUser;
        $this->passwordEncoder = $passwordEncoder;
        $this->slugger = $slugger;
    }

    public function importInDatabase(string $fileName, Service $service): int
    {
        $this->fields = $this->getDatas($fileName);

        $i = 0;

        foreach ($this->fields as $field) {
            $this->field = $field;
            if ($i > 0) {
                $user = $this->createUser($service);
                // Envoie l'email
                $this->notification->createUserAccount($user);
            }
            ++$i;
        }

        // dump($this->existUsers);
        // dd($this->items);
        $this->manager->flush();

        return count($this->items);
    }

    /**
     * Créé l'utilisateur.
     */
    public function createUser(Service $service): ?User
    {
        $user = new User();

        $firstname = $this->field['Prénom'];
        $lastname = $this->field['Nom'];

        $user
            ->setFirstName($firstname)
            ->setLastName($lastname)
            ->setUsername($this->getUsername($firstname, $lastname))
            ->setPassword($this->passwordEncoder->encodePassword($user, bin2hex(random_bytes(8))))
            ->setStatus(1)
            ->setEmail($this->field['Email'])
            ->setphone1($this->field['Téléphone'])
            ->setToken(bin2hex(random_bytes(32)));

        $userExists = $this->userExists($user);

        if ($userExists) {
            $this->existUsers[] = $userExists;

            return $userExists;
        }

        $this->manager->persist($user);

        $serviceUser = (new ServiceUser())
            ->setUser($user)
            ->setService($service);

        $this->manager->persist($serviceUser);

        $this->items[] = $user;

        return $user;
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

        return strtolower($this->slugger->slug($$username.'.'.$lastname));
    }

    /**
     * Vérifie si l'utilisateur existe déjà dans la base de données.
     */
    protected function userExists(User $user)
    {
        return $this->repoUser->findOneBy([
            'username' => $user->getUsername(),
            // 'firstname' => $user->getFirstname(),
            // 'lastname' => $user->getLastname(),
        ]);
    }
}
