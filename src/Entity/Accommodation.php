<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\AccommodationGroup;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccommodationRepository")
 */
class Accommodation
{
    public const ACCOMMODATION_TYPE = [
        1 =>  "Chambre individuelle",
        2 =>  "Chambre collective",
        3 =>  "Chambre d'hôtel",
        4 =>  "Dortoir",
        5 =>  "Logement T1",
        6 =>  "Logement T2",
        7 =>  "Logement T3",
        8 =>  "Logement T4",
        9 =>  "Logement T5",
        10 =>  "Logement T6",
        11 =>  "Logement T7",
        12 =>  "Logement T8",
        13 =>  "Logement T9",
        97 =>  "Autre",
        99 =>  "Non renseigné"
    ];

    public const CONFIGURATION = [
        1 => "Diffus",
        2 => "Regroupé"
    ];

    public const INDIVIDUAL_COLLECTIVE = [
        1 => "Individuel",
        2 => "Collectif",
        97 => "Autre"
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $placesNumber;

    /**
     * @ORM\Column(type="date")
     */
    private $openingDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $closingDate;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $department;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

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
     * @ORM\ManyToOne(targetEntity="App\Entity\Service", inversedBy="accommodations")
     * @ORM\JoinColumn(nullable=false)
     */
    private $service;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="notesCreated")
     */
    private $createdBy;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="notesUpdated")
     */
    private $updatedBy;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Device", inversedBy="accommodations")
     */
    private $device;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AccommodationGroup", mappedBy="accommodation")
     */
    private $accommodationGroups;

    // /**
    //  * @ORM\OneToMany(targetEntity="App\Entity\AccommodationPerson", mappedBy="accommodation")
    //  */
    // private $accommodationPersons;


    public function __construct()
    {
        $this->accommodationGroups = new ArrayCollection();
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

    public function getFullName(): ?string
    {
        return $this->getService()->getName() . " - " . $this->name;
    }


    public function getPlacesNumber(): ?int
    {
        return $this->placesNumber;
    }

    public function setPlacesNumber(?int $placesNumber): self
    {
        $this->placesNumber = $placesNumber;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }


    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getDepartment(): ?string
    {
        return $this->department;
    }

    public function setDepartment(?string $department): self
    {
        $this->department = $department;

        return $this;
    }

    public function getAccommodationType(): ?int
    {
        return $this->accommodationType;
    }

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

    public function getConfigurationToString(): ?string
    {
        return self::CONFIGURATION[$this->configuration];
    }

    public function getIndividualCollective(): ?int
    {
        return $this->individualCollective;
    }

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

    public function getOpeningDate(): ?\DateTimeInterface
    {
        return $this->openingDate;
    }

    public function setOpeningDate(\DateTimeInterface $openingDate): self
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

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): self
    {
        $this->service = $service;

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

    public function getDevice(): ?Device
    {
        return $this->device;
    }

    public function setDevice(?Device $device): self
    {
        $this->device = $device;

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
