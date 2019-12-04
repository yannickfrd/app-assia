<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 *  fields={"username"},
 *  message="Ce nom d'utilisateur existe déjà."
 * )
 */
class User implements UserInterface
{
    public const ROLES = [
        "ROLE_USER" => "Utilisateur",
        "ROLE_ADMIN" => "Administrateur",
        "ROLE_SUPER_ADMIN" => "Administreur général",
    ];
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=100)
     * @Assert\NotBlank(message = "L'email ne doit pas être vide.")
     * @Assert\Email(message="L'adresse email n'est pas valide.")
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $password;
    //  @Assert\Length(min=6, minMessage="Le mot de passe est trop court (6 caractères minimum).")
    //  @Assert\Regex(pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).{6,20}$^", match=true, message="Le mot de passe est invalide.")

    /**
     * @Assert\EqualTo(propertyPath="password", message="Mot de passe différent de la confirmation.")
     */
    private $confirmPassword;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $lastname;
    //* @Assert\NotBlank(message = "Le nom ne doit pas être vide.")

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $firstname;
    //* @Assert\NotBlank(message = "Le prénom ne doit pas être vide.")

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="integer", options={"default":0})
     */
    private $loginCount;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $failureLoginCount;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Person", mappedBy="createdBy")
     */
    private $people;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Person", mappedBy="updatedBy")
     */
    private $peopleUpdated;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GroupPeople", mappedBy="createdBy")
     */
    private $groupPeople;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\GroupPeople", mappedBy="updatedBy")
     */
    private $groupPeopleUpdated;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SupportGrp", mappedBy="createdBy")
     */
    private $supportsGrpCreated;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SupportGrp", mappedBy="updatedBy")
     */
    private $supportsGrpUpdated;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RoleUser", mappedBy="user", cascade={"persist"})
     */
    private $roleUser;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserConnection", mappedBy="user", orphanRemoval=true)
     */
    private $userConnections;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $tokenCreatedAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SupportGrp", mappedBy="referent")
     */
    private $referentSupport;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SupportGrp", mappedBy="referent2")
     */
    private $referent2Support;

    public function __construct()
    {
        $this->people = new ArrayCollection();
        $this->groupPeople = new ArrayCollection();
        $this->supportsGrpCreated = new ArrayCollection();
        $this->roleUser = new ArrayCollection();
        $this->userConnections = new ArrayCollection();
        $this->referentSupport = new ArrayCollection();
        $this->referent2Support = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirmPassword;
    }

    public function setConfirmPassword(string $confirmPassword): self
    {
        $this->confirmPassword = $confirmPassword;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getFullname(): ?string
    {
        return $this->firstname . " " . $this->lastname;
    }

    public function eraseCredentials()
    { }

    public function getSalt()
    { }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(string $role): self
    {
        $this->roles[] = $role;

        return $this;
    }

    public function getLoginCount(): ?int
    {
        return $this->loginCount;
    }

    public function setLoginCount(int $loginCount): self
    {
        $this->loginCount = $loginCount;

        return $this;
    }

    public function getLastLogin(): ?\DateTimeInterface
    {
        return $this->lastLogin;
    }

    public function setLastLogin(?\DateTimeInterface $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getFailureLoginCount(): ?int
    {
        return $this->failureLoginCount;
    }

    public function setFailureLoginCount(?int $failureLoginCount): self
    {
        $this->failureLoginCount = $failureLoginCount;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
    /**
     * @return Collection|Person[]
     */
    public function getPeople(): Collection
    {
        return $this->people;
    }

    public function addPerson(Person $person): self
    {
        if (!$this->people->contains($person)) {
            $this->people[] = $person;
            $person->setCreatedBy($this);
        }

        return $this;
    }

    public function removePerson(Person $person): self
    {
        if ($this->people->contains($person)) {
            $this->people->removeElement($person);
            // set the owning side to null (unless already changed)
            if ($person->getCreatedBy() === $this) {
                $person->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|GroupPeople[]
     */
    public function getGroupPeople(): Collection
    {
        return $this->groupPeople;
    }

    public function addGroupPerson(GroupPeople $groupPerson): self
    {
        if (!$this->groupPeople->contains($groupPerson)) {
            $this->groupPeople[] = $groupPerson;
            $groupPerson->setCreatedBy($this);
        }

        return $this;
    }

    public function removeGroupPerson(GroupPeople $groupPerson): self
    {
        if ($this->groupPeople->contains($groupPerson)) {
            $this->groupPeople->removeElement($groupPerson);
            // set the owning side to null (unless already changed)
            if ($groupPerson->getCreatedBy() === $this) {
                $groupPerson->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SupportGrp[]
     */
    public function getsupportsGrpCreated(): Collection
    {
        return $this->supportsGrpCreated;
    }

    public function addsupportsGrpCreated(SupportGrp $supportsGrpCreated): self
    {
        if (!$this->supportsGrpCreated->contains($supportsGrpCreated)) {
            $this->supportsGrpCreated[] = $supportsGrpCreated;
            $supportsGrpCreated->setCreatedBy($this);
        }

        return $this;
    }

    public function removesupportsGrpCreated(SupportGrp $supportsGrpCreated): self
    {
        if ($this->supportsGrpCreated->contains($supportsGrpCreated)) {
            $this->supportsGrpCreated->removeElement($supportsGrpCreated);
            // set the owning side to null (unless already changed)
            if ($supportsGrpCreated->getCreatedBy() === $this) {
                $supportsGrpCreated->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|RoleUser[]
     */
    public function getRoleUser(): Collection
    {
        return $this->roleUser;
    }

    /**
     * @param RoleUser $roleUser
     * @return self
     */
    public function addRoleUser(RoleUser $roleUser): self
    {
        if (!$this->roleUser->contains($roleUser)) {
            $this->roleUser[] = $roleUser;
            $roleUser->setUser($this);
        }

        return $this;
    }

    public function removeRoleUser(RoleUser $roleUser): self
    {
        if ($this->roleUser->contains($roleUser)) {
            $this->roleUser->removeElement($roleUser);
            // set the owning side to null (unless already changed)
            if ($roleUser->getUser() === $this) {
                $roleUser->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserConnection[]
     */
    public function getUserConnections(): Collection
    {
        return $this->userConnections;
    }

    public function addUserConnection(UserConnection $userConnection): self
    {
        if (!$this->userConnections->contains($userConnection)) {
            $this->userConnections[] = $userConnection;
            $userConnection->setUser($this);
        }

        return $this;
    }

    public function removeUserConnection(UserConnection $userConnection): self
    {
        if ($this->userConnections->contains($userConnection)) {
            $this->userConnections->removeElement($userConnection);
            // set the owning side to null (unless already changed)
            if ($userConnection->getUser() === $this) {
                $userConnection->setUser(null);
            }
        }

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getTokenCreatedAt(): ?\DateTimeInterface
    {
        return $this->tokenCreatedAt;
    }

    public function setTokenCreatedAt(?\DateTimeInterface $tokenCreatedAt): self
    {
        $this->tokenCreatedAt = $tokenCreatedAt;

        return $this;
    }

    /**
     * @return Collection|ReferentSupport[]
     */
    public function getReferentSupport(): Collection
    {
        return $this->referentSupport;
    }

    public function addReferentSupport(SupportGrp $referentSupport): self
    {
        if (!$this->referentSupport->contains($referentSupport)) {
            $this->referentSupport[] = $referentSupport;
            $referentSupport->setReferent($this);
        }

        return $this;
    }

    public function removeReferentSupport(SupportGrp $referentSupport): self
    {
        if ($this->referentSupport->contains($referentSupport)) {
            $this->referentSupport->removeElement($referentSupport);
            // set the owning side to null (unless already changed)
            if ($referentSupport->getReferent() === $this) {
                $referentSupport->setReferent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SupportGrp[]
     */
    public function getReferent2Support(): Collection
    {
        return $this->referent2Support;
    }

    public function addReferent2Support(SupportGrp $referent2Support): self
    {
        if (!$this->referent2Support->contains($referent2Support)) {
            $this->referent2Support[] = $referent2Support;
            $referent2Support->setReferent2($this);
        }

        return $this;
    }

    public function removeReferent2Support(SupportGrp $referent2Support): self
    {
        if ($this->referent2Support->contains($referent2Support)) {
            $this->referent2Support->removeElement($referent2Support);
            // set the owning side to null (unless already changed)
            if ($referent2Support->getReferent2() === $this) {
                $referent2Support->setReferent2(null);
            }
        }

        return $this;
    }
}
