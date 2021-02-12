<?php

namespace App\Entity\Support;

use App\Entity\Organization\Place;
use App\Entity\People\PeopleGroup;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Support\PlaceGroupRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class PlaceGroup
{
    use CreatedUpdatedEntityTrait;
    use SoftDeleteableEntity;

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
     * @ORM\Column(type="date", nullable=true)
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\Place", inversedBy="placeGroups")
     * @Assert\NotNull()
     */
    private $place;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Support\SupportGroup", inversedBy="placeGroups")
     * @ORM\JoinColumn(nullable=true)
     */
    private $supportGroup;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Support\PlacePerson", mappedBy="placeGroup", orphanRemoval=true)
     */
    private $placePeople;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\People\PeopleGroup", inversedBy="placeGroups")
     * @ORM\JoinColumn(nullable=false)
     */
    private $peopleGroup;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $comment;

    public function __construct()
    {
        $this->placePeople = new ArrayCollection();
    }

    public function __toString()
    {
        return strval($this->id);
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
        return $this->endReason ? self::END_REASON[$this->endReason] : null;
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

    public function getPlace(): ?Place
    {
        return $this->place;
    }

    public function setPlace(?Place $place): self
    {
        $this->place = $place;

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

    public function getPeopleGroup(): ?PeopleGroup
    {
        return $this->peopleGroup;
    }

    public function setPeopleGroup(?PeopleGroup $peopleGroup): self
    {
        $this->peopleGroup = $peopleGroup;

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
     * @return PlacePerson[]|Collection|null
     */
    public function getPlacePeople()
    {
        return $this->placePeople;
    }

    public function addPlacePerson(PlacePerson $placePerson): self
    {
        if (!$this->placePeople->contains($placePerson)) {
            $this->placePeople[] = $placePerson;
            $placePerson->setPlaceGroup($this);
        }

        return $this;
    }

    public function removePlacePerson(PlacePerson $placePerson): self
    {
        if ($this->placePeople->contains($placePerson)) {
            $this->placePeople->removeElement($placePerson);
            // set the owning side to null (unless already changed)
            if ($placePerson->getPlaceGroup() === $this) {
                $placePerson->setPlaceGroup(null);
            }
        }

        return $this;
    }
}
