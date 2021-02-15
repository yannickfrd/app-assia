<?php

namespace App\Service\Import;

use App\Entity\Organization\Service;
use App\Entity\Organization\ServiceUser;
use App\Entity\Organization\User;
use App\Notification\UserNotification;
use App\Repository\Organization\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImportDatasUser extends ImportDatas
{
    protected $manager;
    /** @var Request */
    protected $request;
    protected $userNotification;
    protected $slugger;

    protected $fields;
    protected $field;

    protected $repoUser;
    protected $passwordEncoder;

    protected $users = [];
    protected $existUsers = [];

    public function __construct(
        EntityManagerInterface $manager,
        UserNotification $userNotification,
        UserRepository $repoUser,
        UserPasswordEncoderInterface $passwordEncoder,
        SluggerInterface $slugger)
    {
        $this->manager = $manager;
        $this->userNotification = $userNotification;
        $this->repoUser = $repoUser;
        $this->passwordEncoder = $passwordEncoder;
        $this->slugger = $slugger;
    }

    public function importInDatabase(string $fileName, Service $service, ?Request $request = null): int
    {
        $this->fields = $this->getDatas($fileName);
        $this->request = $request;
        $i = 0;

        foreach ($this->fields as $field) {
            $this->field = $field;
            if ($i > 0) {
                $user = $this->createUser($service);
            }
            ++$i;
        }

        $this->manager->flush();

        // Envoie des emails.
        foreach ($this->users as $user) {
            $this->userNotification->newUser($user);
        }

        // dd($this->users);
        return count($this->users);
    }

    /**
     * Créé l'utilisateur.
     */
    public function createUser(Service $service): ?User
    {
        $user = new User();

        $firstname = $this->field['Prénom'];
        $lastname = $this->field['Nom'];
        $status = isset($this->field['Statut']) ? (int) $this->field['Statut'] : 1;
        $role = isset($this->field['Rôle']) ? [$this->field['Rôle']] : [];

        $user
            ->setFirstName($firstname)
            ->setLastName($lastname)
            ->setUsername($this->getUsername($firstname, $lastname))
            ->setPassword($this->passwordEncoder->encodePassword($user, bin2hex(random_bytes(8))))
            ->setStatus($status)
            ->setRoles($role)
            ->setEmail($this->field['Email'])
            ->setphone1($this->field['Téléphone'])
            ->setToken(bin2hex(random_bytes(32)))
            ->setTokenCreatedAt(new \DateTime());

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

        $this->users[] = $user;

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

        $appEnv = $this->request->server->get('APP_ENV');
        $postfix = $appEnv && $appEnv != 'prod' ? '_test' : '';

        return strtolower($this->slugger->slug($username).'.'.$this->slugger->slug($lastname).$postfix);
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
