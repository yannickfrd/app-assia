<?php

namespace App\Service\Import;

use App\Entity\Organization\Service;
use App\Entity\Organization\ServiceUser;
use App\Entity\Organization\User;
use App\Notification\UserNotification;
use App\Repository\Organization\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImportUserDatas extends ImportDatas
{
    protected $em;
    /** @var Request */
    protected $request;
    protected $userNotification;
    protected $slugger;

    protected $fields;
    protected $field;

    protected $userRepo;
    protected $passwordHasher;

    protected $users = [];
    protected $existUsers = [];

    public function __construct(
        EntityManagerInterface $em,
        UserNotification $userNotification,
        UserRepository $userRepo,
        UserPasswordHasherInterface $passwordHasher,
        SluggerInterface $slugger
    ) {
        $this->em = $em;
        $this->userNotification = $userNotification;
        $this->UserRepo = $userRepo;
        $this->passwordHasher = $passwordHasher;
        $this->slugger = $slugger;
    }

    /**
     * Importe les données.
     *
     * @param Collection<Service> $services
     */
    public function importInDatabase(string $fileName, ArrayCollection $services, ?Request $request = null): array
    {
        $this->fields = $this->getDatas($fileName);
        $this->request = $request;
        $i = 0;

        foreach ($this->fields as $field) {
            $this->field = $field;
            if ($i > 0) {
                $user = $this->createUser($services);
            }
            ++$i;
        }

        $this->em->flush();

        // Envoie des emails.
        foreach ($this->users as $user) {
            $this->userNotification->newUser($user);
        }

        return $this->users;
    }

    /**
     * Créé l'utilisateur.
     *
     * @param Collection<Service> $services
     */
    public function createUser($services): ?User
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
            ->setPassword($this->passwordHasher->hashPassword($user, bin2hex(random_bytes(8))))
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

        $this->em->persist($user);

        foreach ($services as $service) {
            $serviceUser = (new ServiceUser())
                ->setUser($user)
                ->setService($service);

            $this->em->persist($serviceUser);
        }

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

        $appEnv = $this->request ? $this->request->server->get('APP_ENV') : 'prod';
        $postfix = $appEnv && 'prod' != $appEnv ? '_test' : '';

        return strtolower($this->slugger->slug($username).'.'.$this->slugger->slug($lastname).$postfix);
    }

    /**
     * Vérifie si l'utilisateur existe déjà dans la base de données.
     */
    protected function userExists(User $user): ?User
    {
        return $this->UserRepo->findOneBy([
            'username' => $user->getUsername(),
            // 'firstname' => $user->getFirstname(),
            // 'lastname' => $user->getLastname(),
        ]);
    }
}
