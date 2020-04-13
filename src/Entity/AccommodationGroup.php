<?php

namespace App\Entity;

use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AccommodationGroupRepository")
 */
class AccommodationGroup
{
    use CreatedUpdatedEntityTrait;

    public const END_REASON = [
        1 => 'Fin du suivi',
        2 => 'Changement de logement/hébergement',
        97 => 'Autre',
        99 => 'Non renseigné',
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotNull()
     */
    private $startDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $endReason;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $commentEndReason;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Accommodation", inversedBy="accommodationGroups")
     * @Assert\NotNull()
     */
    private $accommodation;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SupportGroup", inversedBy="accommodationGroups")
     * @ORM\JoinColumn(nullable=true)
     */
    private $supportGroup;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AccommodationPerson", mappedBy="accommodationGroup", orphanRemoval=true)
     */
    private $accommodationPersons;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GroupPeople", inversedBy="accommodationGroups")
     * @ORM\JoinColumn(nullable=false)
     */
    private $groupPeople;

    public function __construct()
    {
        $this->accommodationPersons = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getEndReason(): ?int
    {
        return $this->endReason;
    }

    /**
     * @Groups("export")
     */
    public function getEndReasonToString(): ?string
    {
        return self::END_REASON[$this->endReason];
    }

    public function setEndReason(?int $endReason): self
    {
        $this->endReason = $endReason;

        return $this;
    }

    public function getCommentEndReason(): ?string
    {
        return $this->commentEndReason;
    }

    public function setCommentEndReason(?string $commentEndReason): self
    {
        $this->commentEndReason = $commentEndReason;

        return $this;
    }

    public function getAccommodation(): ?Accommodation
    {
        return $this->accommodation;
    }

    public function setAccommodation(?Accommodation $accommodation): self
    {
        $this->accommodation = $accommodation;

        return $this;
    }

    public function getSupportGroup(): ?SupportGroup
    {
        return $this->supportGroup;
    }

    public function setSupportGroup(?SupportGroup $supportGroup): self
    {
        $this->supportGroup = $supportGroup;

        return $this;
    }

    /**
     * @return Collection|AccommodationPerson[]
     */
    public function getAccommodationPersons(): ?Collection
    {
        return $this->accommodationPersons;
    }

    public function addAccommodationPerson(AccommodationPerson $accommodationPerson): self
    {
        if (!$this->accommodationPersons->contains($accommodationPerson)) {
            $this->accommodationPersons[] = $accommodationPerson;
            $accommodationPerson->setAccommodationGroup($this);
        }

        return $this;
    }

    public function removeAccommodationPerson(AccommodationPerson $accommodationPerson): self
    {
        if ($this->accommodationPersons->contains($accommodationPerson)) {
            $this->accommodationPersons->removeElement($accommodationPerson);
            // set the owning side to null (unless already changed)
            if ($accommodationPerson->getAccommodationGroup() === $this) {
                $accommodationPerson->setAccommodationGroup(null);
            }
        }

        return $this;
    }

    public function getGroupPeople(): ?GroupPeople
    {
        return $this->groupPeople;
    }

    public function setGroupPeople(?GroupPeople $groupPeople): self
    {
        $this->groupPeople = $groupPeople;

        return $this;
    }
}
