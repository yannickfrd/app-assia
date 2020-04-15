<?php

namespace App\Entity;

use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SupportGroupRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class SupportGroup
{
    use CreatedUpdatedEntityTrait;
    use SoftDeleteableEntity;

    public const STATUS = [
        1 => 'Orientation / Pré-admission',
        2 => 'En cours',
        3 => 'Suspendu',
        4 => 'Terminé',
        5 => 'Autre',
    ];

    public const END_STATUS = [
        001 => 'A la rue - abri de fortune',
        303 => 'Accès à la propriété',
        400 => 'CADA',
        304 => 'Colocation',
        900 => 'Décès',
        700 => 'Départ volontaire de la personne',
        500 => 'Détention',
        105 => 'Dispositif hivernal',
        602 => 'Dispositif de soin ou médical (LAM, autre)',
        502 => 'DLSAP',
        701 => 'Exclusion de la structure',
        106 => 'Foyer maternel',
        010 => 'Hébergé chez des tiers',
        011 => 'Hébergé chez famille',
        100 => 'Hôtel 115',
        101 => 'Hôtel (hors 115)',
        102 => 'Hébergement d’urgence',
        103 => 'Hébergement de stabilisation',
        104 => 'Hébergement d’insertion',
        600 => 'Hôpital',
        401 => 'HUDA',
        601 => 'LHSS',
        200 => 'Logement adapté - ALT',
        201 => 'Logement adapté - FJT',
        202 => 'Logement adapté - FTM',
        203 => 'Logement adapté - Maison relais',
        204 => 'Logement adapté - Résidence sociale',
        205 => 'Logement adapté - RHVS',
        206 => 'Logement adapté - Solibail/IML',
        207 => 'Logement foyer',
        300 => 'Logement privé',
        301 => 'Logement social',
        305 => 'Maison de retraite',
        501 => 'Placement extérieur',
        704 => "Retour dans le pays d'origine",
        302 => 'Sous-location',
        002 => 'Squat',
        97 => 'Autre',
        99 => 'Non renseignée',
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
     * @ORM\Column(type="integer")
     * @Assert\NotNull(message="Le statut doit être renseigné.")
     * @Assert\Range(min = 1, max = 5, minMessage="Le statut doit être renseigné.",  maxMessage="Le statut doit être renseigné.")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="referentSupport")
     */
    private $referent;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="referent2Support")
     */
    private $referent2;

    /**
     * @ORM\Column(type="float", nullable=true, options={"default":1})
     * @Assert\Range(min = 0, max = 10, minMessage="Le coefficient ne peut être inférieur à 0",  maxMessage="Le coefficient ne peut être supérieur à 10")
     */
    private $coefficient = 1;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $endStatus;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $endStatusComment;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $agreement;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="supports")
     */
    private $createdBy; // NE PAS SUPPRIMER

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\GroupPeople", inversedBy="supports")
     * @ORM\JoinColumn(nullable=false)
     */
    private $groupPeople;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SupportPerson", mappedBy="supportGroup", orphanRemoval=true)
     */
    private $supportPerson;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Service", inversedBy="supportGroup")
     */
    private $service;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Device", inversedBy="supportGroup")
     */
    private $device;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Note", mappedBy="supportGroup", cascade={"persist", "remove"})
     */
    private $notes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Rdv", mappedBy="supportGroup", cascade={"persist", "remove"})
     */
    private $rdvs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Document", mappedBy="supportGroup", cascade={"persist", "remove"})
     */
    private $documents;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AccommodationGroup", mappedBy="supportGroup", orphanRemoval=true)
     */
    private $accommodationGroups;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EvaluationGroup", mappedBy="supportGroup", cascade={"persist", "remove"})
     */
    private $evaluationsGroup;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\InitEvalGroup", mappedBy="supportGroup", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     */
    private $initEvalGroup;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\OriginRequest", mappedBy="supportGroup", cascade={"persist", "remove"})
     */
    private $originRequest;

    public function __construct()
    {
        $this->supportPerson = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->rdvs = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->accommodationGroups = new ArrayCollection();
        $this->evaluationsGroup = new ArrayCollection();
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
        if ($endDate) {
            $this->endDate = $endDate;
        }

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function getStatusToString(): ?string
    {
        return $this->status ? self::STATUS[$this->status] : null;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getReferent(): ?User
    {
        return $this->referent;
    }

    public function setReferent(?User $referent): self
    {
        $this->referent = $referent;

        return $this;
    }

    public function getReferent2(): ?User
    {
        return $this->referent2;
    }

    public function setReferent2(?User $referent2): self
    {
        $this->referent2 = $referent2;

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

    public function getAgreement(): ?bool
    {
        return $this->agreement;
    }

    public function setAgreement(?bool $agreement): self
    {
        $this->agreement = $agreement;

        return $this;
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
        return $this->endStatus ? self::END_STATUS[$this->endStatus] : null;
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

    public function getGroupPeople(): ?GroupPeople
    {
        return $this->groupPeople;
    }

    public function setGroupPeople(?GroupPeople $groupPeople): self
    {
        $this->groupPeople = $groupPeople;

        return $this;
    }

    /**
     * @return Collection|SupportPerson[]
     */
    public function getSupportPerson(): ?Collection
    {
        return $this->supportPerson;
    }

    public function addSupportPerson(SupportPerson $supportPerson): self
    {
        if (!$this->supportPerson->contains($supportPerson)) {
            $this->supportPerson[] = $supportPerson;
            $supportPerson->setSupportGroup($this);
        }

        return $this;
    }

    public function removeSupportPerson(SupportPerson $supportPerson): self
    {
        if ($this->supportPerson->contains($supportPerson)) {
            $this->supportPerson->removeElement($supportPerson);
            // set the owning side to null (unless already changed)
            if ($supportPerson->getSupportGroup() === $this) {
                $supportPerson->setSupportGroup(null);
            }
        }

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
     * @return Collection|Note[]
     */
    public function getNotes(): ?Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): self
    {
        if (!$this->notes->contains($note)) {
            $this->notes[] = $note;
            $note->setSupportGroup($this);
        }

        return $this;
    }

    public function removeNote(Note $note): self
    {
        if ($this->notes->contains($note)) {
            $this->notes->removeElement($note);
            // set the owning side to null (unless already changed)
            if ($note->getSupportGroup() === $this) {
                $note->setSupportGroup(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Rdv[]
     */
    public function getRdvs(): ?Collection
    {
        return $this->rdvs;
    }

    public function addRdv(Rdv $rdv): self
    {
        if (!$this->rdvs->contains($rdv)) {
            $this->rdvs[] = $rdv;
            $rdv->setSupportGroup($this);
        }

        return $this;
    }

    public function removeRdv(Rdv $rdv): self
    {
        if ($this->rdvs->contains($rdv)) {
            $this->rdvs->removeElement($rdv);
            // set the owning side to null (unless already changed)
            if ($rdv->getSupportGroup() === $this) {
                $rdv->setSupportGroup(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Document[]
     */
    public function getDocuments(): ?Collection
    {
        return $this->documents;
    }

    public function addDocument(Document $document): self
    {
        if (!$this->documents->contains($document)) {
            $this->documents[] = $document;
            $document->setSupportGroup($this);
        }

        return $this;
    }

    public function removeDocument(Document $document): self
    {
        if ($this->documents->contains($document)) {
            $this->documents->removeElement($document);
            // set the owning side to null (unless already changed)
            if ($document->getSupportGroup() === $this) {
                $document->setSupportGroup(null);
            }
        }

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
            $accommodationGroup->setSupportGroup($this);
        }

        return $this;
    }

    public function removeAccommodationGroup(AccommodationGroup $accommodationGroup): self
    {
        if ($this->accommodationGroups->contains($accommodationGroup)) {
            $this->accommodationGroups->removeElement($accommodationGroup);
            // set the owning side to null (unless already changed)
            if ($accommodationGroup->getSupportGroup() === $this) {
                $accommodationGroup->setSupportGroup(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|EvaluationGroup[]
     */
    public function getEvaluationsGroup(): ?Collection
    {
        return $this->evaluationsGroup;
    }

    public function addEvaluationGroup(EvaluationGroup $evaluationGroup): self
    {
        if (!$this->evaluationsGroup->contains($evaluationGroup)) {
            $this->evaluationsGroup[] = $evaluationGroup;
            $evaluationGroup->setSupportGroup($this);
        }

        return $this;
    }

    public function removeEvaluationGroup(EvaluationGroup $evaluationGroup): self
    {
        if ($this->evaluationsGroup->contains($evaluationGroup)) {
            $this->evaluationsGroup->removeElement($evaluationGroup);
            // set the owning side to null (unless already changed)
            if ($evaluationGroup->getSupportGroup() === $this) {
                $evaluationGroup->setSupportGroup(null);
            }
        }

        return $this;
    }

    public function getInitEvalGroup(): ?InitEvalGroup
    {
        return $this->initEvalGroup;
    }

    public function setInitEvalGroup(InitEvalGroup $initEvalGroup): self
    {
        $this->initEvalGroup = $initEvalGroup;

        // set the owning side of the relation if necessary
        if ($this !== $initEvalGroup->getSupportGroup()) {
            $initEvalGroup->setSupportGroup($this);
        }

        return $this;
    }

    public function getOriginRequest(): ?OriginRequest
    {
        return $this->originRequest;
    }

    public function setOriginRequest(OriginRequest $originRequest): self
    {
        $this->originRequest = $originRequest;

        // set the owning side of the relation if necessary
        if ($originRequest->getSupportGroup() !== $this) {
            $originRequest->setSupportGroup($this);
        }

        return $this;
    }
}
