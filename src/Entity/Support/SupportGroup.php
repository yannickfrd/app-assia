<?php

namespace App\Entity\Support;

use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\InitEvalGroup;
use App\Entity\Organization\Device;
use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Entity\Organization\User;
use App\Entity\People\PeopleGroup;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use App\Entity\Traits\GeoLocationEntityTrait;
use App\Entity\Traits\LocationEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Support\SupportGroupRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class SupportGroup
{
    use CreatedUpdatedEntityTrait;
    use SoftDeleteableEntity;
    use LocationEntityTrait;
    use GeoLocationEntityTrait;

    public const CACHE_SUPPORT_KEY = 'support_group';
    public const CACHE_FULLSUPPORT_KEY = 'support_group_full';
    public const CACHE_SUPPORT_NOTES_KEY = 'support.notes';
    public const CACHE_SUPPORT_RDVS_KEY = 'support.rdvs';
    public const CACHE_SUPPORT_DOCUMENTS_KEY = 'support.documents';
    public const CACHE_SUPPORT_CONTRIBUTIONS_KEY = 'support.contributions';
    public const CACHE_SUPPORT_NB_NOTES_KEY = 'support.notes_count';
    public const CACHE_SUPPORT_NB_RDVS_KEY = 'support.rdvs_count';
    public const CACHE_SUPPORT_NB_DOCUMENTS_KEY = 'support.documents_count';
    public const CACHE_SUPPORT_NB_CONTRIBUTIONS_KEY = 'support.contributions_count';
    public const CACHE_SUPPORT_LAST_RDV_KEY = 'support.last_rdv';
    public const CACHE_SUPPORT_NEXT_RDV_KEY = 'support.next_rdv';

    public const STATUS_IN_PROGRESS = 2;
    public const STATUS_ENDED = 4;
    public const STATUS_PRE_ADD_IN_PROGRESS = 1;
    public const STATUS_PRE_ADD_FAILED = 5;
    public const STATUS_WAITING_LIST = 6;
    public const STATUS_SUSPENDED = 3;
    public const STATUS_OTHER = 97;

    public const STATUS = [
        2 => 'En cours',
        4 => 'Terminé',
        1 => 'Pré-admission en cours',
        5 => 'Pré-admission non aboutie',
        6 => 'Liste d\'attente',
        3 => 'Suspendu',
        97 => 'Autre',
    ];

    public const END_STATUS = [
        001 => 'A la rue - abri de fortune',
        303 => 'Accès à la propriété',
        208 => 'ALTHO',
        400 => 'CADA - dispositif asile',
        304 => 'Colocation',
        900 => 'Décès',
        700 => 'Départ volontaire de la personne',
        500 => 'Détention',
        105 => 'Dispositif hivernal',
        502 => 'DLSAP',
        701 => 'Exclusion de la structure',
        702 => 'Fin du contrat de séjour',
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
        602 => 'Structure de soin ou médical (LAM, autre)',
        97 => 'Autre',
        99 => 'Non renseignée',
    ];

    public const COEFFICIENT_DEFAULT = 1;
    public const COEFFICIENT_DOUBLE = 2;
    public const COEFFICIENT_HALF = 0.5;
    public const COEFFICIENT_THIRD = 0.3;
    public const COEFFICIENT_QUARTER = 0.25;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $startDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $theoreticalEndDate;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("export")
     */
    private $endDate;

    /** @var bool */
    private $endPlace = true;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotNull(message="Le statut doit être renseigné.")
     */
    private $status = 2;

    /**
     * @Groups("export")
     */
    private $statusToString;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\User", inversedBy="referentSupport")
     * @Groups("export")
     */
    private $referent;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\User", inversedBy="referent2Support")
     * @Groups("export")
     */
    private $referent2;

    /**
     * @ORM\Column(type="float", nullable=true, options={"default":1})
     * @Assert\Range(min = 0, max = 10,
     * minMessage="Le coefficient ne peut être inférieur à {{ limit }}",
     * maxMessage="Le coefficient ne peut être supérieur à {{ limit }}")
     * @Groups("export")
     */
    private $coefficient = self::COEFFICIENT_DEFAULT;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups("export")
     */
    private $endStatus;

    /**
     * @Groups("export")
     */
    private $endStatusToString;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("export")
     */
    private $endStatusComment;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $endLocationAddress;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $endLocationCity;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $endLocationZipcode;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $agreement;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $nbPeople;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups("view")
     */
    private $comment;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\User", inversedBy="supports")
     * @MaxDepth(1)
     */
    private $createdBy; // NE PAS SUPPRIMER

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\People\PeopleGroup", inversedBy="supports")
     * @ORM\JoinColumn(nullable=false)
     * @MaxDepth(1)
     */
    private $peopleGroup;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Support\SupportPerson", mappedBy="supportGroup", cascade={"persist", "remove"}, orphanRemoval=true)
     * @MaxDepth(1)
     */
    private $supportPeople;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\Service", inversedBy="supportGroup")
     * @Groups("export")
     */
    private $service;

    /**
     * @ORM\ManyToOne(targetEntity=SubService::class, inversedBy="supportGroup")
     * @Groups("export")
     */
    private $subService;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\Device", inversedBy="supportGroup")
     * @Groups("export")
     */
    private $device;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Support\Note", mappedBy="supportGroup", cascade={"persist", "remove"})
     * @MaxDepth(1)
     */
    private $notes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Support\Rdv", mappedBy="supportGroup", cascade={"persist", "remove"})
     * @MaxDepth(1)
     */
    private $rdvs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Support\Document", mappedBy="supportGroup", cascade={"persist", "remove"})
     * @MaxDepth(1)
     */
    private $documents;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Support\PlaceGroup", mappedBy="supportGroup", orphanRemoval=true, cascade={"persist", "remove"})
     * @MaxDepth(1)
     */
    private $placeGroups;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Evaluation\EvaluationGroup", mappedBy="supportGroup", cascade={"persist", "remove"})
     * @MaxDepth(1)
     */
    private $evaluationsGroup;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Evaluation\InitEvalGroup", mappedBy="supportGroup", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @MaxDepth(1)
     */
    private $initEvalGroup;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Support\OriginRequest", mappedBy="supportGroup", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @MaxDepth(1)
     */
    private $originRequest;

    /**
     * @ORM\OneToMany(targetEntity=Contribution::class, mappedBy="supportGroup", orphanRemoval=true)
     */
    private $contributions;

    /**
     * @ORM\OneToOne(targetEntity=Avdl::class, mappedBy="supportGroup", cascade={"persist", "remove"})
     */
    private $avdl;

    /**
     * @ORM\OneToOne(targetEntity=HotelSupport::class, mappedBy="supportGroup", cascade={"persist", "remove"})
     */
    private $hotelSupport;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $siSiaoId;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $nbChildrenUnder3years;

    public function __construct()
    {
        $this->supportPeople = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->rdvs = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->placeGroups = new ArrayCollection();
        $this->evaluationsGroup = new ArrayCollection();
        $this->contributions = new ArrayCollection();
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

    public function getTheoreticalEndDate(): ?\DateTimeInterface
    {
        return $this->theoreticalEndDate;
    }

    public function setTheoreticalEndDate(?\DateTimeInterface $theoreticalEndDate): self
    {
        $this->theoreticalEndDate = $theoreticalEndDate;

        return $this;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        // if ($endDate) {
        $this->endDate = $endDate;
        // }

        return $this;
    }

    public function getEndPlace(): ?bool
    {
        return $this->endPlace;
    }

    public function setEndPlace(?bool $endPlace): self
    {
        $this->endPlace = $endPlace;

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

    public function getStatusHotelToString(): ?string
    {
        return $this->status ? HotelSupport::STATUS[$this->status] : null;
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

    public function getEndLocationAddress(): ?string
    {
        return $this->endLocationAddress;
    }

    public function setEndLocationAddress(?string $endLocationAddress): self
    {
        $this->endLocationAddress = $endLocationAddress;

        return $this;
    }

    public function getEndLocationCity(): ?string
    {
        return $this->endLocationCity;
    }

    public function setEndLocationCity(?string $endLocationCity): self
    {
        $this->endLocationCity = $endLocationCity;

        return $this;
    }

    public function getEndLocationZipcode(): ?string
    {
        return $this->endLocationZipcode;
    }

    public function setEndLocationZipcode(?string $endLocationZipcode): self
    {
        $this->endLocationZipcode = $endLocationZipcode;

        return $this;
    }

    public function getNbPeople(): ?int
    {
        return $this->nbPeople;
    }

    public function setNbPeople(?int $nbPeople): self
    {
        $this->nbPeople = $nbPeople;

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

    public function getPeopleGroup(): ?PeopleGroup
    {
        return $this->peopleGroup;
    }

    public function setPeopleGroup(?PeopleGroup $peopleGroup): self
    {
        $this->peopleGroup = $peopleGroup;

        return $this;
    }

    /**
     * @return SupportPerson[]|Collection|null
     */
    public function getSupportPeople()
    {
        return $this->supportPeople;
    }

    public function addSupportPerson(SupportPerson $supportPerson): self
    {
        if (!$this->supportPeople->contains($supportPerson)) {
            $this->supportPeople[] = $supportPerson;
            $supportPerson->setSupportGroup($this);
        }

        return $this;
    }

    public function removeSupportPerson(SupportPerson $supportPerson): self
    {
        if ($this->supportPeople->contains($supportPerson)) {
            $this->supportPeople->removeElement($supportPerson);
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

    /**
     * @return Note[]|Collection|null
     */
    public function getNotes()
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
     * @return Rdv[]|Collection|null
     */
    public function getRdvs()
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
     * @return Document[]|Collection|null
     */
    public function getDocuments()
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
     * @return PlaceGroup[]|Collection|null
     */
    public function getPlaceGroups()
    {
        return $this->placeGroups;
    }

    public function addPlaceGroup(PlaceGroup $placeGroup): self
    {
        if (!$this->placeGroups->contains($placeGroup)) {
            $this->placeGroups[] = $placeGroup;
            $placeGroup->setSupportGroup($this);
        }

        return $this;
    }

    public function removePlaceGroup(PlaceGroup $placeGroup): self
    {
        if ($this->placeGroups->contains($placeGroup)) {
            $this->placeGroups->removeElement($placeGroup);
            // set the owning side to null (unless already changed)
            if ($placeGroup->getSupportGroup() === $this) {
                $placeGroup->setSupportGroup(null);
            }
        }

        return $this;
    }

    /**
     * @return EvaluationGroup[]|Collection|null
     */
    public function getEvaluationsGroup()
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

    public function setOriginRequest(?OriginRequest $originRequest): self
    {
        if ($originRequest->getId() || false === $this->objectIsEmpty($originRequest)) {
            $this->originRequest = $originRequest;
        }

        // set the owning side of the relation if necessary
        if ($originRequest->getSupportGroup() !== $this) {
            $originRequest->setSupportGroup($this);
        }

        return $this;
    }

    protected function objectIsEmpty(object $originRequest)
    {
        foreach ((array) $originRequest as $value) {
            if ($value) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return Contribution[]|Collection|null
     */
    public function getContributions()
    {
        return $this->contributions;
    }

    public function addContribution(Contribution $contribution): self
    {
        if (!$this->contributions->contains($contribution)) {
            $this->contributions[] = $contribution;
            $contribution->setSupportGroup($this);
        }

        return $this;
    }

    public function removeContribution(Contribution $contribution): self
    {
        if ($this->contributions->contains($contribution)) {
            $this->contributions->removeElement($contribution);
            // set the owning side to null (unless already changed)
            if ($contribution->getSupportGroup() === $this) {
                $contribution->setSupportGroup(null);
            }
        }

        return $this;
    }

    public function getAvdl(): ?Avdl
    {
        return $this->avdl;
    }

    public function setAvdl(Avdl $avdl): self
    {
        $this->avdl = $avdl;

        // set the owning side of the relation if necessary
        if ($avdl->getSupportGroup() !== $this) {
            $avdl->setSupportGroup($this);
        }

        return $this;
    }

    public function getHotelSupport(): ?HotelSupport
    {
        return $this->hotelSupport;
    }

    public function setHotelSupport(HotelSupport $hotelSupport): self
    {
        $this->hotelSupport = $hotelSupport;

        // set the owning side of the relation if necessary
        if ($hotelSupport->getSupportGroup() !== $this) {
            $hotelSupport->setSupportGroup($this);
        }

        return $this;
    }

    public function getSiSiaoId(): ?int
    {
        return $this->siSiaoId;
    }

    public function setSiSiaoId(?int $siSiaoId): self
    {
        $this->siSiaoId = $siSiaoId;

        return $this;
    }

    public function getNbChildrenUnder3years(): ?int
    {
        return $this->nbChildrenUnder3years;
    }

    public function setNbChildrenUnder3years(?int $nbChildrenUnder3years): self
    {
        $this->nbChildrenUnder3years = $nbChildrenUnder3years;

        return $this;
    }
}
