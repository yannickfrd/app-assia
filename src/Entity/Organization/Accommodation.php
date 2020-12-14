<?php

namespace App\Entity\Organization;

use App\Entity\Support\AccommodationGroup;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use App\Entity\Traits\DisableEntityTrait;
use App\Entity\Traits\GeoLocationEntityTrait;
use App\Entity\Traits\LocationEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Organization\AccommodationRepository")
 * @UniqueEntity(
 *     fields={"name", "service"},
 *     errorPath="name",
 *     message="Ce groupe de places existe déjà !")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class Accommodation
{
    use CreatedUpdatedEntityTrait;
    use LocationEntityTrait;
    use GeoLocationEntityTrait;
    use SoftDeleteableEntity;
    use DisableEntityTrait;

    public const ACCOMMODATION_TYPE = [
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
    private $accommodationType;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\Service", inversedBy="accommodations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $service;

    /**
     * @ORM\ManyToOne(targetEntity=SubService::class, inversedBy="accommodations")
     */
    private $subService;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\Device", inversedBy="accommodations")
     */
    private $device;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $rentAmt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Support\AccommodationGroup", mappedBy="accommodation", orphanRemoval=true)
     */
    private $accommodationGroups;

    public function __construct()
    {
        $this->accommodationGroups = new ArrayCollection();
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

    public function getAccommodationType(): ?int
    {
        return $this->accommodationType;
    }

    /**
     * @Groups("export")
     */
    public function getAccommodationTypeToString(): ?string
    {
        return self::ACCOMMODATION_TYPE[$this->accommodationType];
    }

    public function setAccommodationType(?int $accommodationType): self
    {
        $this->accommodationType = $accommodationType;

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
     * @return Collection|AccommodationGroup[]
     */
    public function getAccommodationGroups(): ?Collection
    {
        return $this->accommodationGroups;
    }

    public function addAccommodationGroup(AccommodationGroup $accommodationGroup): self
    {
        if (!$this->accommodationGroups->contains($accommodationGroup)) {
            $this->accommodationGroups[] = $accommodationGroup;
            $accommodationGroup->setAccommodation($this);
        }

        return $this;
    }

    public function removeAccommodationGroup(AccommodationGroup $accommodationGroup): self
    {
        if ($this->accommodationGroups->contains($accommodationGroup)) {
            $this->accommodationGroups->removeElement($accommodationGroup);
            // set the owning side to null (unless already changed)
            if ($accommodationGroup->getAccommodation() === $this) {
                $accommodationGroup->setAccommodation(null);
            }
        }

        return $this;
    }
}
