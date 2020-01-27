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
    public const STATUS = [
        1 => "Travailleur social",
        2 => "Coordinatrice/teur",
        3 => "Chef·fe de service",
        4 => "Directrice/teur",
        5 => "Administratif",
        6 => "Chargé·e de mission",
        7 => "Stagiaire",
        98 => "Autre"
    ];

    public const ROLES = [
        "ROLE_USER" => "Utilisateur",
        "ROLE_ADMIN" => "Administrateur",
        "ROLE_SUPER_ADMIN" => "Administrateur général"
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
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $phone2;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Regex(pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).{8,20}$^", match=true, message="Le mot de passe est invalide.")
     */
    private $password;

    /**
     * @Assert\EqualTo(propertyPath="password", message="Le mot de passe différent de la confirmation.")
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
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="people")
     */
    private $createdBy;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="peopleUpdated")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $updatedBy;

    /**
     * @ORM\Column(type="integer", options={"default":0})
     */
    private $loginCount = 0;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastLogin;

    /**
     * @ORM\Column(type="integer", options={"default":0})
     */
    private $failureLoginCount = 0;

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
     * @ORM\OneToMany(targetEntity="App\Entity\SupportGroup", mappedBy="createdBy")
     */
    private $supportsGroupCreated;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SupportGroup", mappedBy="updatedBy")
     */
    private $supportsGroupUpdated;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ServiceUser", mappedBy="user", orphanRemoval=true, cascade={"persist"})
     */
    private $serviceUser;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UserConnection", mappedBy="user", orphanRemoval=true)
     */
    private $userConnections;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $token;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $active;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $tokenCreatedAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SupportGroup", mappedBy="referent")
     */
    private $referentSupport;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SupportGroup", mappedBy="referent2")
     */
    private $referent2Support;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Note", mappedBy="createdBy")
     */
    private $notesCreated;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Note", mappedBy="updatedBy")
     */
    private $notesUpdated;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Rdv", mappedBy="createdBy")
     */
    private $rdvs;

    public function __construct()
    {
        $this->people = new ArrayCollection();
        $this->groupPeople = new ArrayCollection();
        $this->supportsGroupCreated = new ArrayCollection();
        $this->serviceUser = new ArrayCollection();
        $this->userConnections = new ArrayCollection();
        $this->referentSupport = new ArrayCollection();
        $this->referent2Support = new ArrayCollection();
        $this->notesCreated = new ArrayCollection();
        $this->rdvs = new ArrayCollection();
    }

    // public function __toString()
    // {
    //     return strval($this->id);
    // }

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

    public function getPhone2(): ?string
    {
        return $this->phone2;
    }

    public function setPhone2(?string $phone2): self
    {
        $this->phone2 = $phone2;

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

    public function getInitials(): ?string
    {
        return substr($this->firstname, 0, 1) . substr($this->lastname, 0, 1);
    }

    public function eraseCredentials()
    {
    }

    public function getSalt()
    {
    }


    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusList()
    {
        return self::STATUS[$this->status];
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = "ROLE_USER";

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = [];

        foreach ($roles as $role) {
            $this->roles[] = $role;
        }

        // $this->roles[] = $role;

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

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedBy(): ?User
    {
        return $this->updatedBy;
    }

    public function setUpdatedBy(?User $updatedBy): self
    {
        $this->updatedBy = $updatedBy;

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
     * @return Collection|SupportGroup[]
     */
    public function getsupportsGroupCreated(): Collection
    {
        return $this->supportsGroupCreated;
    }

    public function addsupportsGroupCreated(SupportGroup $supportsGroupCreated): self
    {
        if (!$this->supportsGroupCreated->contains($supportsGroupCreated)) {
            $this->supportsGroupCreated[] = $supportsGroupCreated;
            $supportsGroupCreated->setCreatedBy($this);
        }

        return $this;
    }

    public function removesupportsGroupCreated(SupportGroup $supportsGroupCreated): self
    {
        if ($this->supportsGroupCreated->contains($supportsGroupCreated)) {
            $this->supportsGroupCreated->removeElement($supportsGroupCreated);
            // set the owning side to null (unless already changed)
            if ($supportsGroupCreated->getCreatedBy() === $this) {
                $supportsGroupCreated->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ServiceUser[]
     */
    public function getServiceUser(): Collection
    {
        return $this->serviceUser;
    }

    /**
     * @param ServiceUser $serviceUser
     * @return self
     */
    public function addServiceUser(ServiceUser $serviceUser): self
    {
        if (!$this->serviceUser->contains($serviceUser)) {
            $this->serviceUser[] = $serviceUser;
            $serviceUser->setUser($this);
        }

        return $this;
    }

    public function removeServiceUser(ServiceUser $serviceUser): self
    {
        if ($this->serviceUser->contains($serviceUser)) {
            $this->serviceUser->removeElement($serviceUser);
            // set the owning side to null (unless already changed)
            if ($serviceUser->getUser() === $this) {
                $serviceUser->setUser(null);
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

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return Collection|ReferentSupport[]
     */
    public function getReferentSupport(): Collection
    {
        return $this->referentSupport;
    }

    public function addReferentSupport(SupportGroup $referentSupport): self
    {
        if (!$this->referentSupport->contains($referentSupport)) {
            $this->referentSupport[] = $referentSupport;
            $referentSupport->setReferent($this);
        }

        return $this;
    }

    public function removeReferentSupport(SupportGroup $referentSupport): self
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
     * @return Collection|SupportGroup[]
     */
    public function getReferent2Support(): Collection
    {
        return $this->referent2Support;
    }

    public function addReferent2Support(SupportGroup $referent2Support): self
    {
        if (!$this->referent2Support->contains($referent2Support)) {
            $this->referent2Support[] = $referent2Support;
            $referent2Support->setReferent2($this);
        }

        return $this;
    }

    public function removeReferent2Support(SupportGroup $referent2Support): self
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

    /**
     * @return Collection|Note[]
     */
    public function getNotesCreated(): Collection
    {
        return $this->notesCreated;
    }

    public function addNote(Note $note): self
    {
        if (!$this->notesCreated->contains($note)) {
            $this->notesCreated[] = $note;
            $note->setCreatedBy($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notesCreated->contains($note)) {
            $this->notesCreated->removeElement($note);
            // set the owning side to null (unless already changed)
            if ($note->getCreatedBy() === $this) {
                $note->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Rdv[]
     */
    public function getRdvs(): Collection
    {
        return $this->rdvs;
    }

    public function addRdv(Rdv $rdv): self
    {
        if (!$this->rdvs->contains($rdv)) {
            $this->rdvs[] = $rdv;
            $rdv->setCreatedBy($this);
        }

        return $this;
    }

    public function removeRdv(Rdv $rdv): self
    {
        if ($this->rdvs->contains($rdv)) {
            $this->rdvs->removeElement($rdv);
            // set the owning side to null (unless already changed)
            if ($rdv->getCreatedBy() === $this) {
                $rdv->setCreatedBy(null);
            }
        }

        return $this;
    }
}
