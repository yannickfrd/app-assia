<?php

namespace App\Entity\Organization;

use App\Entity\Support\SupportGroup;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use App\Entity\Traits\DisableEntityTrait;
use App\Form\Utils\Choices;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Organization\DeviceRepository")
 * @UniqueEntity(
 *  fields={"name"},
 *  message="Ce dispositif existe déjà."
 * )
 */
class Device
{
    use CreatedUpdatedEntityTrait;
    use DisableEntityTrait;

    public const CODES = [
        9 => '10 000 LA BA',
        13 => '10 000 LA BD',
        1 => 'ALT',
        2 => 'ALTHO',
        8 => 'ASLL',
        3 => 'ASLLT',
        11 => 'AVDL',
        10 => 'AVDL DALO',
        4 => 'AVDL hors DALO',
        15 => 'ASE Mise à l\'abri',
        16 => 'ASE hébergement',
        19 => 'Acommpagnement hôtel',
        20 => 'Intervention d\'urgence hôtel',
        6 => 'HU - Diffus',
        22 => 'HU - Regroupé',
        23 => 'HU - Regroupé ACSE',
        24 => 'HU - Regroupé HAVC',
        25 => 'HU - Regroupé PE',
        26 => 'HU hiver',
        5 => 'Insertion - regroupé',
        7 => 'Maison relais',
        100 => 'Accompagnement social',
        200 => 'Équipe mobile',
        201 => 'Équipe mobile véhiculée',
        202 => 'Équipe mobile pédestre',
        203 => 'Équipe mobile psy',
        301 => 'Tiers lieu alimentaire',
        400 => 'Asile | CADA',
        401 => 'Asile | HUDA',
        402 => 'Asile | AT-SA',
        403 => 'Asile | CAES',
        501 => 'Justice | Placement extérieur',
        502 => 'Justice | DLSAP',
        503 => 'Justice | Contrôle judiciaire socio-judiciaire (CJSE)',
        504 => 'Justice | Composition pénale (CP)',
        505 => 'Justice | Enquête de personnalité (EP)',
        506 => 'Justice | Sursis de mise à l\'épreuve (SME)',
        507 => 'Justice | CHRS sortants de prison',
        508 => 'Justice | Suivi socio-judiciaire (SSJ)',
        601 => 'LHSS',
    ];

    public const AVDL_DALO = 10;
    public const AVDL_HORS_DALO = 4;
    public const ASE_MAB = 15;
    public const ASE_HEB = 16;
    public const HOTEL_SUPPORT = 19;
    public const HOTEL_URG = 20;

    public const VAR_CONTRIBUTION = 1;
    public const RENT_CONTRIBUTION = 2;

    public const CONTRIBUTION_TYPE = [
        1 => 'Pourcentage des ressources',
        2 => 'Loyer (montant fixé dans fiche logement)',
        3 => 'Autre',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank();
     * @Groups("export")
     */
    private $name;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Assert\Range(min = 0, max = 10,
     * minMessage="Le coefficient ne peut être inférieur à {{ limit }}",
     * maxMessage="Le coefficient ne peut être supérieur à {{ limit }}")
     */
    private $coefficient;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Assert\NotNull();
     */
    private $code;

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
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $justice;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Organization\ServiceDevice", mappedBy="device", orphanRemoval=true, cascade={"persist"})
     */
    private $serviceDevices;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Organization\Place", mappedBy="device")
     */
    private $places;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Support\SupportGroup", mappedBy="device")
     */
    private $supportGroup;

    /**
     * @ORM\OneToMany(targetEntity=UserDevice::class, mappedBy="device", orphanRemoval=true)
     */
    private $userDevices;

    public function __construct()
    {
        $this->serviceDevices = new ArrayCollection();
        $this->places = new ArrayCollection();
        $this->supportGroup = new ArrayCollection();
        $this->userDevices = new ArrayCollection();
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

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function getCodeToString(): ?string
    {
        return $this->code ? self::CODES[$this->code] : null;
    }

    public function setCode(?int $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getCoefficient(): ?float
    {
        return $this->coefficient;
    }

    public function setCoefficient(?float $coefficient): self
    {
        $this->coefficient = $coefficient;

        return $this;
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

    public function getPlaceToString(): ?string
    {
        return $this->place ? Choices::YES_NO[$this->place] : null;
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

    public function getJustice(): ?int
    {
        return $this->justice;
    }

    public function setJustice(?int $justice): self
    {
        $this->justice = $justice;

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
     * @return Collection<Device>|Device[]|null
     */
    public function getServiceDevices(): ?Collection
    {
        return $this->serviceDevices;
    }

    public function addServiceDevice(ServiceDevice $serviceDevice): self
    {
        if (!$this->serviceDevices->contains($serviceDevice)) {
            $this->serviceDevices[] = $serviceDevice;
            $serviceDevice->setDevice($this);
        }

        return $this;
    }

    public function removeServiceDevice(ServiceDevice $serviceDevice): self
    {
        if ($this->serviceDevices->contains($serviceDevice)) {
            $this->serviceDevices->removeElement($serviceDevice);
            // set the owning side to null (unless already changed)
            if ($serviceDevice->getDevice() === $this) {
                $serviceDevice->setDevice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<Place>|Place[]|null
     */
    public function getPlaces(): ?Collection
    {
        return $this->places;
    }

    public function addPlace(Place $place): self
    {
        if (!$this->places->contains($place)) {
            $this->places[] = $place;
            $place->setDevice($this);
        }

        return $this;
    }

    public function removePlace(Place $place): self
    {
        if ($this->places->contains($place)) {
            $this->places->removeElement($place);
            // set the owning side to null (unless already changed)
            if ($place->getDevice() === $this) {
                $place->setDevice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<SupportGroup>|SupportGroup[]|null
     */
    public function getSupportGroup(): ?Collection
    {
        return $this->supportGroup;
    }

    public function addSupportGroup(SupportGroup $supportGroup): self
    {
        if (!$this->supportGroup->contains($supportGroup)) {
            $this->supportGroup[] = $supportGroup;
            $supportGroup->setDevice($this);
        }

        return $this;
    }

    public function removeSupportGroup(SupportGroup $supportGroup): self
    {
        if ($this->supportGroup->contains($supportGroup)) {
            $this->supportGroup->removeElement($supportGroup);
            // set the owning side to null (unless already changed)
            if ($supportGroup->getDevice() === $this) {
                $supportGroup->setDevice(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<UserDevice>|UserDevice[]|null
     */
    public function getUserDevices(): ?Collection
    {
        return $this->userDevices;
    }

    public function addUserDevice(UserDevice $userDevice): self
    {
        if (!$this->userDevices->contains($userDevice)) {
            $this->userDevices[] = $userDevice;
            $userDevice->setDevice($this);
        }

        return $this;
    }

    public function removeUserDevice(UserDevice $userDevice): self
    {
        if ($this->userDevices->contains($userDevice)) {
            $this->userDevices->removeElement($userDevice);
            // set the owning side to null (unless already changed)
            if ($userDevice->getDevice() === $this) {
                $userDevice->setDevice(null);
            }
        }

        return $this;
    }
}
