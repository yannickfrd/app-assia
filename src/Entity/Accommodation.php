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
    public function getAccommodationGroups(): Collection
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

    /**
     * @return Collection|AccommodationPerson[]
     */
    public function getAccommodationPersons(): Collection
    {
        return $this->accommodationPersons;
    }

    public function addAccommodationPerson(AccommodationPerson $accommodationPerson): self
    {
        if (!$this->accommodationPersons->contains($accommodationPerson)) {
            $this->accommodationPersons[] = $accommodationPerson;
            $accommodationPerson->setAccommodation($this);
        }

        return $this;
    }

    public function removeAccommodationPerson(AccommodationPerson $accommodationPerson): self
    {
        if ($this->accommodationPersons->contains($accommodationPerson)) {
            $this->accommodationPersons->removeElement($accommodationPerson);
            // set the owning side to null (unless already changed)
            if ($accommodationPerson->getAccommodation() === $this) {
                $accommodationPerson->setAccommodation(null);
            }
        }

        return $this;
    }
}
