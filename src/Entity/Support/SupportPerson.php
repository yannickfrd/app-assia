<?php

namespace App\Entity\Support;

use App\Entity\Evaluation\EvalInitPerson;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use App\Entity\Traits\DurationSupportTrait;
use App\Form\Utils\Choices;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Support\SupportPersonRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class SupportPerson
{
    use CreatedUpdatedEntityTrait;
    use DurationSupportTrait;
    use SoftDeleteableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"show_support_person", "exportable"})
     */
    private $id;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $role;

    /**
     * @Groups({"export", "exportable"})
     */
    private $roleToString;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $head;

    /**
     * @Groups({"export", "exportable"}).
     */
    private $headToString;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Assert\NotNull(message="La date de début ne doit pas être vide.")
     * @Groups({"export", "exportable"})
     */
    private $startDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"export", "exportable"})
     */
    private $endDate;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotNull(message="Le statut doit être renseigné.")
     * @Assert\Range(min = 1, max = 5, minMessage="Le statut doit être renseigné.",  maxMessage="Le statut doit être renseigné.")
     */
    private $status;

    /**
     * @Groups({"export", "exportable"})
     */
    private $statusToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"export", "exportable"})
     */
    private $endReason;

    /**
     * @Groups({"export", "exportable"})
     */
    private $endReasonToString;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $endStatus;

    /**
     * @Groups({"export", "exportable"})
     */
    private $endStatusToString;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"export", "exportable"})
     */
    private $endStatusComment;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\People\Person", inversedBy="supports")
     * @ORM\JoinColumn(nullable=false)
     * @Groups("show_person")
     */
    private $person;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Support\SupportGroup", inversedBy="supportPeople")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supportGroup;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Support\Note", mappedBy="supportPerson")
     */
    private $notes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Evaluation\EvaluationPerson", mappedBy="supportPerson", cascade={"persist", "remove"})
     */
    private $evaluations;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Evaluation\EvalInitPerson", mappedBy="supportPerson", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     */
    private $evalInitPerson;

    /**
     * @ORM\OneToMany(targetEntity=PlacePerson::class, mappedBy="supportPerson", cascade={"persist", "remove"})
     */
    private $placesPerson;

    // /**
    //  * @ORM\ManyToMany(targetEntity=Task::class, mappedBy="supportPeople")
    //  */
    // private $tasks;

    public function __construct()
    {
        $this->notes = new ArrayCollection();
        $this->evaluations = new ArrayCollection();
        $this->placesPerson = new ArrayCollection();
        // $this->tasks = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): ?int
    {
        return $this->role;
    }

    public function getRoleToString(): ?string
    {
        return $this->role ? RolePerson::ROLE[$this->role] : null;
    }

    public function setRole(?int $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getHead(): ?bool
    {
        return $this->head;
    }

    public function getHeadToString(): ?string
    {
        return Choices::YES_NO_BOOLEAN[$this->head];
    }

    public function setHead(?bool $head): self
    {
        $this->head = $head;

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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function getStatusToString(): ?string
    {
        return $this->status ? SupportGroup::STATUS[$this->status] : null;
    }

    public function getStatusHotelToString(): ?string
    {
        return $this->status ? HotelSupport::STATUS[$this->status] : null;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getEndReason(): ?int
    {
        return $this->endReason;
    }

    public function setEndReason(?int $endReason): self
    {
        $this->endReason = $endReason;

        return $this;
    }

    public function getEndReasonToString(): ?string
    {
        return $this->endReason ? SupportGroup::END_REASONS[$this->endReason] : null;
    }

    public function getEndStatus(): ?int
    {
        return $this->endStatus;
    }

    public function setEndStatus(?int $endStatus): self
    {
        $this->endStatus = $endStatus;

        return $this;
    }

    public function getEndStatusToString(): ?string
    {
        return $this->endStatus ? SupportGroup::END_STATUS[$this->endStatus] : null;
    }

    public function getEndStatusComment(): ?string
    {
        return $this->endStatusComment;
    }

    public function setEndStatusComment(?string $endStatusComment): self
    {
        $this->endStatusComment = $endStatusComment;

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

    public function getPerson(): ?Person
    {
        return $this->person;
    }

    public function setPerson(?Person $person): self
    {
        $this->person = $person;

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
     * @return Collection<Note>|null
     */
    public function getNotes(): ?Collection
    {
        return $this->notes;
    }

    /**
     * @return Collection<EvaluationPerson>|null
     */
    public function getEvaluations(): ?Collection
    {
        return $this->evaluations;
    }

    public function getFirstEvaluation(): ?EvaluationPerson
    {
        $evaluation = $this->evaluations->first();

        return false !== $evaluation ? $evaluation : null;
    }

    public function addEvaluationPerson(EvaluationPerson $evaluationPerson): self
    {
        if (!$this->evaluations->contains($evaluationPerson)) {
            $this->evaluations[] = $evaluationPerson;
            $evaluationPerson->setSupportPerson($this);
        }

        return $this;
    }

    public function removeEvaluationPerson(EvaluationPerson $evaluationPerson): self
    {
        if ($this->evaluations->contains($evaluationPerson)) {
            $this->evaluations->removeElement($evaluationPerson);
            // set the owning side to null (unless already changed)
            if ($evaluationPerson->getSupportPerson() === $this) {
                $evaluationPerson->setSupportPerson(null);
            }
        }

        return $this;
    }

    public function getEvalInitPerson(): ?EvalInitPerson
    {
        return $this->evalInitPerson;
    }

    public function setEvalInitPerson(?EvalInitPerson $evalInitPerson): self
    {
        $this->evalInitPerson = $evalInitPerson;

        // set the owning side of the relation if necessary
        if ($this !== $evalInitPerson->getSupportPerson()) {
            $evalInitPerson->setSupportPerson($this);
        }

        return $this;
    }

    /**
     * @return Collection<PlacePerson>|null
     */
    public function getPlacesPerson(): ?Collection
    {
        return $this->placesPerson;
    }

    public function addPlacesPerson(PlacePerson $placesPerson): self
    {
        if (!$this->placesPerson->contains($placesPerson)) {
            $this->placesPerson[] = $placesPerson;
            $placesPerson->setSupportPerson($this);
        }

        return $this;
    }

    public function removePlacesPerson(PlacePerson $placesPerson): self
    {
        if ($this->placesPerson->contains($placesPerson)) {
            $this->placesPerson->removeElement($placesPerson);
            // set the owning side to null (unless already changed)
            if ($placesPerson->getSupportPerson() === $this) {
                $placesPerson->setSupportPerson(null);
            }
        }

        return $this;
    }

    /** @Groups("exportable") */
    public function getPlaceStartDate(): ?\DateTimeInterface
    {
        if (0 === $this->getPlacesPerson()->count()) {
            return null;
        }

        $placesStartDates = [];

        foreach ($this->getPlacesPerson() as $placePerson) {
            $placesStartDates[] = $placePerson->getStartDate();
        }

        return min($placesStartDates);
    }

    /** @Groups("exportable") */
    public function getPlaceEndDate(): ?\DateTimeInterface
    {
        if (0 === $this->getPlacesPerson()->count()) {
            return null;
        }

        $placesEndDates = [];

        foreach ($this->getPlacesPerson() as $placePerson) {
            $placesEndDates[] = $placePerson->getEndDate();
        }

        return max($placesEndDates);
    }

    /** @Groups("exportable") */
    public function getPlaceEndReason(): ?string
    {
        if (0 === $this->getPlacesPerson()->count()) {
            return null;
        }

        return $this->getPlacesPerson()->last()->getEndReasonToString();
    }

    /** @Groups("exportable") */
    public function getPlacesNames(): ?string
    {
        if (0 === $this->getPlacesPerson()->count()) {
            return null;
        }

        $placesNames = [];

        foreach ($this->getPlacesPerson() as $placePerson) {
            $placesNames[] = $placePerson->getPlaceGroup()->getPlace()->getName();
        }

        return (string) join(', ', $placesNames);
    }
}
