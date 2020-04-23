<?php

namespace App\Entity;

use App\Entity\Traits\ContactEntityTrait;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use App\Entity\Traits\DisableEntityTrait;
use App\Entity\Traits\LocationEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ServiceRepository")
 * @UniqueEntity(
 *     fields={"name"},
 *     message="Ce service existe déjà !")
 */
class Service
{
    use ContactEntityTrait;
    use LocationEntityTrait;
    use CreatedUpdatedEntityTrait;
    use DisableEntityTrait;

    public const SUPPORT_ACCESS = [
        1 => 'Uniquement le référent du suivi',
        2 => 'Tou·te·s les salarié·e·s du service',
        3 => 'Tou·te·s les salarié·e·s du pôle',
        4 => "Tou·te·s les salarié·e·s de l'association",
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le nom du service ne doit pas être vide.")
     * Groups("export")
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ServiceUser", mappedBy="service", orphanRemoval=true, cascade={"persist"})
     */
    private $serviceUser;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pole", inversedBy="services", cascade={"persist"})
     * @Assert\NotNull(message="Le pôle est obligatoire.")
     */
    private $pole;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SupportGroup", mappedBy="service")
     */
    private $supportGroup;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $supportAccess;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $preAdmission;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $accommodation;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $justice;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $finessId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $siretId;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $openingDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $closingDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ServiceDevice", mappedBy="service", orphanRemoval=true, cascade={"persist"})
     */
    private $serviceDevices;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Accommodation", mappedBy="service")
     */
    private $accommodations;

    // /**
    //  * @ORM\ManyToMany(targetEntity="App\Entity\Organization", mappedBy="service")
    //  */
    // private $organizations;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $chief;

    public function __construct()
    {
        $this->serviceUser = new ArrayCollection();
        $this->supportGroup = new ArrayCollection();
        $this->serviceDevices = new ArrayCollection();
        $this->accommodations = new ArrayCollection();
        // $this->organizations = new ArrayCollection();
    }

    public function __toString()
    {
        return strval($this->id);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|ServiceUser[]
     */
    public function getserviceUser(): ?Collection
    {
        return $this->serviceUser;
    }

    public function addServiceUser(ServiceUser $serviceUser): self
    {
        if (!$this->serviceUser->contains($serviceUser)) {
            $this->serviceUser[] = $serviceUser;
            $serviceUser->setService($this);
        }

        return $this;
    }

    public function removeServiceUser(ServiceUser $serviceUser): self
    {
        if ($this->serviceUser->contains($serviceUser)) {
            $this->serviceUser->removeElement($serviceUser);
            // set the owning side to null (unless already changed)
            if ($serviceUser->getService() === $this) {
                $serviceUser->setService(null);
            }
        }

        return $this;
    }

    public function getPole(): ?Pole
    {
        return $this->pole;
    }

    public function setPole(?Pole $pole): self
    {
        $this->pole = $pole;

        return $this;
    }

    /**
     * @return Collection|SupportGroup[]
     */
    public function getSupportGroup(): ?Collection
    {
        return $this->supportGroup;
    }

    public function addSupportGroup(SupportGroup $supportGroup): self
    {
        if (!$this->supportGroup->contains($supportGroup)) {
            $this->supportGroup[] = $supportGroup;
            $supportGroup->setService($this);
        }

        return $this;
    }

    public function removeSupportGroup(SupportGroup $supportGroup): self
    {
        if ($this->supportGroup->contains($supportGroup)) {
            $this->supportGroup->removeElement($supportGroup);
            // set the owning side to null (unless already changed)
            if ($supportGroup->getService() === $this) {
                $supportGroup->setService(null);
            }
        }

        return $this;
    }

    public function getSupportAccess(): ?int
    {
        return $this->supportAccess;
    }

    public function setSupportAccess(?int $supportAccess): self
    {
        $this->supportAccess = $supportAccess;

        return $this;
    }

    public function getSupportAccessToString(): ?string
    {
        return $this->supportAccess ? self::SUPPORT_ACCESS[$this->supportAccess] : null;
    }

    public function getPreAdmission(): ?bool
    {
        return $this->preAdmission;
    }

    public function setPreAdmission(?bool $preAdmission): self
    {
        $this->preAdmission = $preAdmission;

        return $this;
    }

    public function getAccommodation(): ?bool
    {
        return $this->accommodation;
    }

    public function setAccommodation(?bool $accommodation): self
    {
        $this->accommodation = $accommodation;

        return $this;
    }

    public function getJustice(): ?bool
    {
        return $this->justice;
    }

    public function setJustice(?bool $justice): self
    {
        $this->justice = $justice;

        return $this;
    }

    public function getFinessId(): ?string
    {
        return $this->finessId;
    }

    public function setFinessId(?string $finessId): self
    {
        $this->finessId = $finessId;

        return $this;
    }

    public function getSiretId(): ?string
    {
        return $this->siretId;
    }

    public function setSiretId(?string $siretId): self
    {
        $this->siretId = $siretId;

        return $this;
    }

    public function getOpeningDate(): ?\DateTimeInterface
    {
        return $this->openingDate;
    }

    public function setOpeningDate(?\DateTimeInterface $openingDate): self
    {
        $this->openingDate = $openingDate;

        return $this;
    }

    public function getClosingDate(): ?\DateTimeInterface
    {
        return $this->closingDate;
    }

    public function setClosingDate(?\DateTimeInterface $closingDate): self
    {
        $this->closingDate = $closingDate;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return Collection|ServiceDevice[]
     */
    public function getServiceDevices(): ?Collection
    {
        return $this->serviceDevices;
    }

    public function addServiceDevice(ServiceDevice $serviceDevice): self
    {
        if (!$this->serviceDevices->contains($serviceDevice)) {
            $this->serviceDevices[] = $serviceDevice;
            $serviceDevice->setService($this);
        }

        return $this;
    }

    public function removeServiceDevice(ServiceDevice $serviceDevice): self
    {
        if ($this->serviceDevices->contains($serviceDevice)) {
            $this->serviceDevices->removeElement($serviceDevice);
            // set the owning side to null (unless already changed)
            if ($serviceDevice->getService() === $this) {
                $serviceDevice->setService(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Accommodation[]
     */
    public function getAccommodations(): ?Collection
    {
        return $this->accommodations;
    }

    public function addAccommodation(Accommodation $accommodation): self
    {
        if (!$this->accommodations->contains($accommodation)) {
            $this->accommodations[] = $accommodation;
            $accommodation->setService($this);
        }

        return $this;
    }

    public function removeAccommodation(Accommodation $accommodation): self
    {
        if ($this->accommodations->contains($accommodation)) {
            $this->accommodations->removeElement($accommodation);
            // set the owning side to null (unless already changed)
            if ($accommodation->getService() === $this) {
                $accommodation->setService(null);
            }
        }

        return $this;
    }

    // /**
    //  * @return Collection|Organization[]
    //  */
    // public function getOrganizations(): ?Collection
    // {
    //     return $this->organizations;
    // }

    // public function addOrganization(Organization $organization): self
    // {
    //     if (!$this->organizations->contains($organization)) {
    //         $this->organizations[] = $organization;
    //         $organization->addService($this);
    //     }

    //     return $this;
    // }

    // public function removeOrganization(Organization $organization): self
    // {
    //     if ($this->organizations->contains($organization)) {
    //         $this->organizations->removeElement($organization);
    //         $organization->removeService($this);
    //     }

    //     return $this;
    // }

    public function getChief(): ?User
    {
        return $this->chief;
    }

    public function setChief(?User $chief): self
    {
        $this->chief = $chief;

        return $this;
    }
}
