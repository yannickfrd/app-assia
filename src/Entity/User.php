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
     * @Assert\Length(min=6, minMessage="Le mot de passe est trop court (6 caractères minimum).")
     */
    private $password;

    /**
     * @Assert\EqualTo(propertyPath="password", message="Mot de passe différent de la confirmation.")
     */
    private $confirmPassword;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(message = "Le nom ne doit pas être vide.")
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(message = "Le prénom ne doit pas être vide.")
     */
    private $firstname;

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
     * @ORM\OneToMany(targetEntity="App\Entity\SocialSupportGrp", mappedBy="createdBy")
     */
    private $socialSupportsGrpCreated;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SocialSupportGrp", mappedBy="updatedBy")
     */
    private $socialSupportsGrpUpdated;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\RoleUser", mappedBy="user", orphanRemoval=true)
     */
    private $roleUser;

    public function __construct()
    {
        $this->people = new ArrayCollection();
        $this->peopleUpdated = new ArrayCollection();
        $this->groupPeople = new ArrayCollection();
        $this->groupPeopleUpdated = new ArrayCollection();
        $this->socialSupportsGrpCreated = new ArrayCollection();
        $this->socialSupportsGrpUpdated = new ArrayCollection();
        $this->roleUser = new ArrayCollection();
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
     * @return Collection|Person[]
     */
    public function getPeopleUpdated(): Collection
    {
        return $this->peopleUpdated;
    }

    public function addPeopleUpdated(Person $peopleUpdated): self
    {
        if (!$this->peopleUpdated->contains($peopleUpdated)) {
            $this->peopleUpdated[] = $peopleUpdated;
            $peopleUpdated->setUpdatedBy($this);
        }

        return $this;
    }

    public function removePeopleUpdated(Person $peopleUpdated): self
    {
        if ($this->peopleUpdated->contains($peopleUpdated)) {
            $this->peopleUpdated->removeElement($peopleUpdated);
            // set the owning side to null (unless already changed)
            if ($peopleUpdated->getUpdatedBy() === $this) {
                $peopleUpdated->setUpdatedBy(null);
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
     * @return Collection|GroupPeople[]
     */
    public function getGroupPeopleUpdated(): Collection
    {
        return $this->groupPeopleUpdated;
    }

    public function addGroupPeopleUpdated(GroupPeople $groupPeopleUpdated): self
    {
        if (!$this->groupPeopleUpdated->contains($groupPeopleUpdated)) {
            $this->groupPeopleUpdated[] = $groupPeopleUpdated;
            $groupPeopleUpdated->setUpdatedBy($this);
        }

        return $this;
    }

    public function removeGroupPeopleUpdated(GroupPeople $groupPeopleUpdated): self
    {
        if ($this->groupPeopleUpdated->contains($groupPeopleUpdated)) {
            $this->groupPeopleUpdated->removeElement($groupPeopleUpdated);
            // set the owning side to null (unless already changed)
            if ($groupPeopleUpdated->getUpdatedBy() === $this) {
                $groupPeopleUpdated->setUpdatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SocialSupportGrp[]
     */
    public function getsocialSupportsGrpCreated(): Collection
    {
        return $this->socialSupportsGrpCreated;
    }

    public function addsocialSupportsGrpCreated(SocialSupportGrp $socialSupportsGrpCreated): self
    {
        if (!$this->socialSupportsGrpCreated->contains($socialSupportsGrpCreated)) {
            $this->socialSupportsGrpCreated[] = $socialSupportsGrpCreated;
            $socialSupportsGrpCreated->setCreatedBy($this);
        }

        return $this;
    }

    public function removesocialSupportsGrpCreated(SocialSupportGrp $socialSupportsGrpCreated): self
    {
        if ($this->socialSupportsGrpCreated->contains($socialSupportsGrpCreated)) {
            $this->socialSupportsGrpCreated->removeElement($socialSupportsGrpCreated);
            // set the owning side to null (unless already changed)
            if ($socialSupportsGrpCreated->getCreatedBy() === $this) {
                $socialSupportsGrpCreated->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|SocialSupportGrp[]
     */
    public function getsocialSupportsGrpUpdated(): Collection
    {
        return $this->socialSupportsGrpUpdated;
    }

    public function addsocialSupportsGrpUpdated(SocialSupportGrp $socialSupportsGrpUpdated): self
    {
        if (!$this->socialSupportsGrpUpdated->contains($socialSupportsGrpUpdated)) {
            $this->socialSupportsGrpUpdated[] = $socialSupportsGrpUpdated;
            $socialSupportsGrpUpdated->setUpdatedBy($this);
        }

        return $this;
    }

    public function removesocialSupportsGrpUpdated(SocialSupportGrp $socialSupportsGrpUpdated): self
    {
        if ($this->socialSupportsGrpUpdated->contains($socialSupportsGrpUpdated)) {
            $this->socialSupportsGrpUpdated->removeElement($socialSupportsGrpUpdated);
            // set the owning side to null (unless already changed)
            if ($socialSupportsGrpUpdated->getUpdatedBy() === $this) {
                $socialSupportsGrpUpdated->setUpdatedBy(null);
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
}
