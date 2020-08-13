<?php

namespace App\Entity;

use App\Service\Phone;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\ContactEntityTrait;
use Doctrine\Common\Collections\Collection;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use App\Entity\Traits\DisableEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
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
    use ContactEntityTrait;
    use CreatedUpdatedEntityTrait;
    use DisableEntityTrait;

    public const STATUS_SOCIAL_WORKER = 1;
    public const STATUS_COORDO = 2;
    public const STATUS_CHIEF = 3;
    public const STATUS_DIRECTOR = 4;
    public const STATUS_ADMINISTRATIVE = 5;

    public const STATUS = [
        1 => 'Travailleur social',
        5 => 'Administratif',
        6 => 'Chargé·e de mission',
        3 => 'Chef·fe de service',
        2 => 'Coordinatrice/teur',
        4 => 'Directrice/teur',
        7 => 'Stagiaire',
        8 => 'Responsable informatique',
        97 => 'Autre',
    ];

    public const ROLES = [
        'ROLE_USER' => 'Utilisateur',
        'ROLE_ADMIN' => 'Administrateur',
        'ROLE_SUPER_ADMIN' => 'Administrateur général',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=100, nullable=false)
     * @Assert\NotBlank(message = "L'email ne doit pas être vide.")
     * @Assert\Email(message="L'adresse email n'est pas valide.")
     */
    private $email; // NE PAS SUPPRIMER

    /**
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     * @Assert\Regex(pattern="^0[1-9]([-._/ ]?[0-9]{2}){4}$^", match=true, message="Le numéro de téléphone est incorrect.")
     */
    private $phone1; // NE PAS SUPPRIMER

    /**
     * @ORM\Column(type="string", length=255)
     *@Assert\NotBlank()
     * @Assert\Regex(pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).{8,}$^", match=true, message="Le mot de passe est invalide.")
     */
    private $password;

    /**
     * @var string
     */
    private $plainPassword;

    /**
     * @Assert\EqualTo(propertyPath="password", message="Le mot de passe différent de la confirmation.")
     */
    private $confirmPassword;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(message = "Le nom ne doit pas être vide.")
     * @Assert\Length(min=2, max=50,
     * minMessage="Le nom est trop court ({{ limit }} caractères min).",
     * maxMessage="Le nom est trop long ({{ limit }} caractères max).")
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank(message = "Le prénom ne doit pas être vide.")
     * @Assert\Length(min=2, max=50,
     * minMessage="Le prénom est trop court ({{ limit }} caractères min).",
     * maxMessage="Le prénom est trop long ({{ limit }} caractères max).")
     */
    private $firstname;

    /**
     * @Groups({"export", "view"})
     */
    private $fullname;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

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
     * @ORM\OneToMany(targetEntity="App\Entity\SupportGroup", mappedBy="createdBy")
     */
    private $supports;

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
    private $notes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Rdv", mappedBy="createdBy")
     */
    private $rdvs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Document", mappedBy="createdBy")
     */
    private $documents;

    /**
     * @ORM\OneToMany(targetEntity=UserDevice::class, mappedBy="user", orphanRemoval=true, cascade={"persist"})
     */
    private $userDevices;

    public function __construct()
    {
        $this->supports = new ArrayCollection();
        $this->serviceUser = new ArrayCollection();
        $this->userConnections = new ArrayCollection();
        $this->referentSupport = new ArrayCollection();
        $this->referent2Support = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->rdvs = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->userDevices = new ArrayCollection();
    }

    public function __toString()
    {
        return strval($this->id);
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(?string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirmPassword;
    }

    public function setConfirmPassword(?string $confirmPassword): self
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
        $this->lastname = strtoupper($lastname);

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = ucfirst($firstname);

        return $this;
    }

    public function getFullname(): ?string
    {
        return $this->lastname.' '.$this->firstname;
    }

    public function getInitials(): ?string
    {
        return substr($this->firstname, 0, 1).substr($this->lastname, 0, 1);
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

    public function getStatusToString(): ?string
    {
        return $this->status ? self::STATUS[$this->status] : null;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

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

    /**
     * @return Collection|SupportGroup[]
     */
    public function getSupports(): ?Collection
    {
        return $this->supports;
    }

    public function addSupports(SupportGroup $supports): self
    {
        if (!$this->supports->contains($supports)) {
            $this->supports[] = $supports;
            $supports->setCreatedBy($this);
        }

        return $this;
    }

    public function removeSupports(SupportGroup $supports): self
    {
        if ($this->supports->contains($supports)) {
            $this->supports->removeElement($supports);
            // set the owning side to null (unless already changed)
            if ($supports->getCreatedBy() === $this) {
                $supports->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|ServiceUser[]
     */
    public function getServiceUser(): ?Collection
    {
        return $this->serviceUser;
    }

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
    public function getUserConnections(): ?Collection
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
     * @return Collection|Referent[]
     */
    public function getReferentSupport(): ?Collection
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
    public function getReferent2Support(): ?Collection
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
    public function getNotes(): ?Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setCreatedBy($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notes->contains($note)) {
            $this->notes->removeElement($note);
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
    public function getRdvs(): ?Collection
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

    /**
     * @return Collection|Document[]
     */
    public function getDocuments(): ?Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setCreatedBy($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->contains($document)) {
            $this->documents->removeElement($document);
            // set the owning side to null (unless already changed)
            if ($document->getCreatedBy() === $this) {
                $document->setCreatedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|UserDevice[]
     */
    public function getUserDevices(): Collection
    {
        return $this->userDevices;
    }

    public function addUserDevice(UserDevice $userDevice): self
    {
        if (!$this->userDevices->contains($userDevice)) {
            $this->userDevices[] = $userDevice;
            $userDevice->setUser($this);
        }

        return $this;
    }

    public function removeUserDevice(UserDevice $userDevice): self
    {
        if ($this->userDevices->contains($userDevice)) {
            $this->userDevices->removeElement($userDevice);
            // set the owning side to null (unless already changed)
            if ($userDevice->getUser() === $this) {
                $userDevice->setUser(null);
            }
        }

        return $this;
    }
}
