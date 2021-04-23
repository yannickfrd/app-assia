<?php

namespace App\Entity\Organization;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Support\PlaceGroup;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Traits\DisableEntityTrait;
use App\Entity\Traits\LocationEntityTrait;
use Doctrine\Common\Collections\Collection;
use App\Entity\Traits\GeoLocationEntityTrait;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Organization\PlaceRepository")
 * @UniqueEntity(
 *     fields={"name", "service"},
 *     errorPath="name",
 *     message="Ce groupe de places existe déjà !")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class Place
{
    use CreatedUpdatedEntityTrait;
    use LocationEntityTrait;
    use GeoLocationEntityTrait;
    use SoftDeleteableEntity;
    use DisableEntityTrait;

    public const PLACE_TYPE = [
        1 => 'Chambre individuelle',
        2 => 'Chambre collective',
        3 => "Chambre d'hôtel",
        4 => 'Dortoir',
        5 => 'Logement T1',
        6 => 'Logement T2',
        7 => 'Logement T3',
        8 => 'Logement T4',
        9 => 'Logement T5',
        10 => 'Logement T6',
        11 => 'Logement T7',
        12 => 'Logement T8',
        13 => 'Logement T9',
        14 => 'Pavillon',
        97 => 'Autre',
        99 => 'Non renseigné',
    ];

    public const CONFIGURATION = [
        1 => 'Diffus',
        2 => 'Regroupé',
    ];

    public const INDIVIDUAL_COLLECTIVE = [
        1 => 'Individuel',
        2 => 'Collectif',
        97 => 'Autre',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(name="places_number", type="integer", nullable=true)
     */
    private $nbPlaces;

    /**
     * @ORM\Column(name="opening_date", type="date", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\Column(name="closing_date", type="date", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $placeType;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $configuration;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $individualCollective;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\Service", inversedBy="places")
     * @ORM\JoinColumn(nullable=false)
     */
    private $service;

    /**
     * @ORM\ManyToOne(targetEntity=SubService::class, inversedBy="places")
     */
    private $subService;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\Device", inversedBy="places")
     */
    private $device;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $rentAmt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Support\PlaceGroup", mappedBy="place", orphanRemoval=true)
     */
    private $placeGroups;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $area;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $lessor;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $analyticId;

    public function __construct()
    {
        $this->placeGroups = new ArrayCollection();
    }

    public function __toString()
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

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getFullname(): ?string
    {
        return $this->getService()->getName().' - '.$this->name;
    }

    public function getNbPlaces(): ?int
    {
        return $this->nbPlaces;
    }

    public function setNbPlaces(?int $nbPlaces): self
    {
        $this->nbPlaces = $nbPlaces;

        return $this;
    }

    public function getPlaceType(): ?int
    {
        return $this->placeType;
    }

    /**
     * @Groups("export")
     */
    public function getPlaceTypeToString(): ?string
    {
        return $this->placeType ? self::PLACE_TYPE[$this->placeType] : null;
    }

    public function setPlaceType(?int $placeType): self
    {
        $this->placeType = $placeType;

        return $this;
    }

    public function getConfiguration(): ?int
    {
        return $this->configuration;
    }

    public function setConfiguration(?int $configuration): self
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * @Groups("export")
     */
    public function getConfigurationToString(): ?string
    {
        return self::CONFIGURATION[$this->configuration];
    }

    public function getIndividualCollective(): ?int
    {
        return $this->individualCollective;
    }

    /**
     * @Groups("export")
     */
    public function getIndividualCollectiveToString(): ?string
    {
        return self::INDIVIDUAL_COLLECTIVE[$this->individualCollective];
    }

    public function setIndividualCollective(?int $individualCollective): self
    {
        $this->individualCollective = $individualCollective;

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

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function getSubService(): ?SubService
    {
        return $this->subService;
    }

    public function setSubService(?SubService $subService): self
    {
        $this->subService = $subService;

        return $this;
    }

    public function getDevice(): ?Device
    {
        return $this->device;
    }

    public function setDevice(?Device $device): self
    {
        $this->device = $device;

        return $this;
    }

    public function getRentAmt(): ?float
    {
        return $this->rentAmt;
    }

    public function setRentAmt(?float $rentAmt): self
    {
        $this->rentAmt = $rentAmt;

        return $this;
    }

    /**
     * @return Collection<PlaceGroup>
     */
    public function getPlaceGroups()
    {
        return $this->placeGroups;
    }

    public function addPlaceGroup(PlaceGroup $placeGroup): self
    {
        if (!$this->placeGroups->contains($placeGroup)) {
            $this->placeGroups[] = $placeGroup;
            $placeGroup->setPlace($this);
        }

        return $this;
    }

    public function removePlaceGroup(PlaceGroup $placeGroup): self
    {
        if ($this->placeGroups->contains($placeGroup)) {
            $this->placeGroups->removeElement($placeGroup);
            // set the owning side to null (unless already changed)
            if ($placeGroup->getPlace() === $this) {
                $placeGroup->setPlace(null);
            }
        }

        return $this;
    }

    public function getArea(): ?float
    {
        return $this->area;
    }

    public function setArea(?float $area): self
    {
        $this->area = $area;

        return $this;
    }

    public function getLessor(): ?string
    {
        return $this->lessor;
    }

    public function setLessor(?string $lessor): self
    {
        $this->lessor = $lessor;

        return $this;
    }

    public function getAnalyticId(): ?string
    {
        return $this->analyticId;
    }

    public function setAnalyticId(?string $analyticId): self
    {
        $this->analyticId = $analyticId;

        return $this;
    }
}
