<?php

namespace App\Entity\Organization;

use App\Entity\Organization\Traits\TagTrait;
use App\Entity\Support\SupportGroup;
use App\Entity\Traits\ContactEntityTrait;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use App\Entity\Traits\DisableEntityTrait;
use App\Entity\Traits\LocationEntityTrait;
use App\Form\Utils\Choices;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Organization\ServiceRepository")
 * @UniqueEntity(
 *     fields={"name"},
 *     message="Ce service existe déjà !"
 * )
 */
class Service
{
    use ContactEntityTrait;
    use LocationEntityTrait;
    use TagTrait;
    use CreatedUpdatedEntityTrait;
    use DisableEntityTrait;

    public const CACHE_INDICATORS_KEY = 'stats.service';
    public const CACHE_SERVICE_PLACES_KEY = 'service.places';
    public const CACHE_SERVICE_SUBSERVICES_KEY = 'service.sub_services';
    public const CACHE_SERVICE_USERS_KEY = 'service.users';
    public const CACHE_SERVICE_TAGS_KEY = 'service.tags';

    public const SERVICE_TYPE_HEB = 1;
    public const SERVICE_TYPE_AVDL = 2;
    public const SERVICE_TYPE_HOTEL = 3;
    public const SERVICE_TYPE_ASYLUM = 4;

    public const SERVICE_TYPE = [
        1 => 'Accompagnement social',
        2 => 'AVDL',
        3 => 'Accompagnement hôtel',
        4 => 'Asile',
        5 => 'Socio-judiciaire',
        6 => 'Socio-médical',
        7 => 'IAE',
        97 => 'Autre',
    ];

    public const SUPPORT_ACCESS = [
        1 => 'Uniquement l\'interveant du suivi',
        2 => 'Tou·te·s les salarié·e·s du service',
        3 => 'Tou·te·s les salarié·e·s du pôle',
        4 => "Tou·te·s les salarié·e·s de l'association",
    ];

    public const FIX_PERCENT_CONTRIBUTION = 3;
    public const VAR_PERCENT_CONTRIBUTION = 1;
    public const RENT_CONTRIBUTION = 2;

    public const CONTRIBUTION_TYPE = [
        3 => 'Pourcentage fixe selon les ressources',
        1 => 'Pourcentage variable selon le dispositif',
        2 => 'Loyer (montant fixé dans fiche logement)',
        4 => 'Autre',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"export", "show_service"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Le nom du service ne doit pas être vide.")
     * @Groups({"export", "show_service"})
     */
    private $name;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Organization\ServiceUser", mappedBy="service", orphanRemoval=true, cascade={"persist"})
     */
    private $serviceUser;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\Pole", inversedBy="services", cascade={"persist"})
     * @Assert\NotNull(message="Le pôle est obligatoire.")
     */
    private $pole;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Support\SupportGroup", mappedBy="service")
     */
    private $supportGroup;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $supportAccess;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $preAdmission;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $place;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $contribution;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $contributionType;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $contributionRate = 0.1;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $minRestToLive;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $coefficient;

    /**
     * @ORM\Column(type="smallint", nullable=true)
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
     * @ORM\Column(name="opening_date", type="date", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\Column(name="closing_date", type="date", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Organization\ServiceDevice", mappedBy="service", orphanRemoval=true, cascade={"persist"})
     */
    private $serviceDevices;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Organization\Place", mappedBy="service")
     */
    private $places;

    // /**
    //  * @ORM\ManyToMany(targetEntity="App\Entity\Organization\Organization", mappedBy="service")
    //  */
    // private $organizations;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\User")
     */
    private $chief;

    /**
     * @ORM\ManyToMany(targetEntity=Organization::class, inversedBy="services")
     */
    private $organizations;

    /**
     * @ORM\OneToMany(targetEntity=SubService::class, mappedBy="service", orphanRemoval=true)
     */
    private $subServices;

    /**
     * @var Collection<Tag>|null
     * @ORM\ManyToMany(targetEntity=Tag::class, inversedBy="services")
     * @ORM\OrderBy({"name": "ASC"})
     */
    protected $tags;

    /**
     * @ORM\OneToOne(targetEntity=ServiceSetting::class, cascade={"persist", "remove"})
     */
    private $setting;

    public function __construct()
    {
        $this->serviceUser = new ArrayCollection();
        $this->supportGroup = new ArrayCollection();
        $this->serviceDevices = new ArrayCollection();
        $this->places = new ArrayCollection();
        $this->organizations = new ArrayCollection();
        $this->subServices = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->id;
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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function getTypeToString(): ?string
    {
        return $this->type ? self::SERVICE_TYPE[$this->type] : null;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<User>|null
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

    /**
     * @return Collection<User>|null
     */
    public function getUsers(): ?Collection
    {
        $users = new ArrayCollection();

        foreach ($this->serviceUser as $serviceUser) {
            $users->add($serviceUser->getUser());
        }

        return $users;
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
     * @return Collection<SupportGroup>|null
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

    public function getPreAdmission(): ?int
    {
        return $this->preAdmission;
    }

    public function setPreAdmission(?int $preAdmission): self
    {
        $this->preAdmission = $preAdmission;

        return $this;
    }

    public function getPlace(): ?int
    {
        return $this->place;
    }

    public function getServiceTypeDefault(): array
    {
        return self::SERVICE_TYPE;
    }

    public function setPlace(?int $place): self
    {
        $this->place = $place;

        return $this;
    }

    public function getContribution(): ?int
    {
        return $this->contribution;
    }

    public function setContribution(?int $contribution): self
    {
        $this->contribution = $contribution;

        return $this;
    }

    public function getContributionType(): ?int
    {
        return $this->contributionType;
    }

    public function getContributionTypeToString(): ?string
    {
        return $this->contributionType ? self::CONTRIBUTION_TYPE[$this->contributionType] : null;
    }

    public function setContributionType(?int $contributionType): self
    {
        $this->contributionType = $contributionType;

        return $this;
    }

    public function getContributionRate(): ?float
    {
        return $this->contributionRate;
    }

    public function setContributionRate(?float $contributionRate): self
    {
        $this->contributionRate = $contributionRate;

        return $this;
    }

    public function getMinRestToLive(): ?float
    {
        return $this->minRestToLive;
    }

    public function setMinRestToLive(?float $minRestToLive): self
    {
        $this->minRestToLive = $minRestToLive;

        return $this;
    }

    public function getCoefficient(): ?int
    {
        return $this->coefficient;
    }

    public function getCoefficientToString(): ?string
    {
        return $this->contributionType ? Choices::YES_NO[$this->contributionType] : null;
    }

    public function setCoefficient(?int $coefficient): self
    {
        $this->coefficient = $coefficient;

        return $this;
    }

    public function getJustice(): ?int
    {
        return $this->justice;
    }

    public function setJustice(?int $justice): self
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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

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
     * @return Collection<ServiceDevice>|null
     */
    public function getServiceDevices(): ?Collection
    {
        return $this->serviceDevices;
    }

    /**
     * @return Collection<Device>|null
     */
    public function getDevices(): ?Collection
    {
        $devices = new ArrayCollection();

        foreach ($this->serviceDevices as $serviceDevice) {
            $devices->add($serviceDevice->getDevice());
        }

        return $devices;
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
     * @return Collection<Place>|null
     */
    public function getPlaces(): ?Collection
    {
        return $this->places;
    }

    public function addPlace(Place $place): self
    {
        if (!$this->places->contains($place)) {
            $this->places[] = $place;
            $place->setService($this);
        }

        return $this;
    }

    public function removePlace(Place $place): self
    {
        if ($this->places->contains($place)) {
            $this->places->removeElement($place);
            // set the owning side to null (unless already changed)
            if ($place->getService() === $this) {
                $place->setService(null);
            }
        }

        return $this;
    }

    public function getChief(): ?User
    {
        return $this->chief;
    }

    public function setChief(?User $chief): self
    {
        $this->chief = $chief;

        return $this;
    }

    /**
     * @return Collection<Organization>|null
     */
    public function getOrganizations(): ?Collection
    {
        return $this->organizations;
    }

    public function addOrganization(Organization $organization): self
    {
        if (!$this->organizations->contains($organization)) {
            $this->organizations[] = $organization;
        }

        return $this;
    }

    public function removeOrganization(Organization $organization): self
    {
        if ($this->organizations->contains($organization)) {
            $this->organizations->removeElement($organization);
        }

        return $this;
    }

    /**
     * @return Collection<SubService>|null
     */
    public function getSubServices(): ?Collection
    {
        return $this->subServices;
    }

    public function addSubService(SubService $subService): self
    {
        if (!$this->subServices->contains($subService)) {
            $this->subServices[] = $subService;
            $subService->setService($this);
        }

        return $this;
    }

    public function removeSubService(SubService $subService): self
    {
        if ($this->subServices->contains($subService)) {
            $this->subServices->removeElement($subService);
            // set the owning side to null (unless already changed)
            if ($subService->getService() === $this) {
                $subService->setService(null);
            }
        }

        return $this;
    }

    public function getSetting(): ?ServiceSetting
    {
        return $this->setting;
    }

    public function setSetting(?ServiceSetting $setting): self
    {
        $this->setting = $setting;

        return $this;
    }
}
