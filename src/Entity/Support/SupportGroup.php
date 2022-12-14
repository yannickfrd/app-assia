<?php

namespace App\Entity\Support;

use App\Entity\Evaluation\EvalInitGroup;
use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Event\Rdv;
use App\Entity\Event\Task;
use App\Entity\Organization\Device;
use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Entity\Organization\User;
use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\Traits\ArchivedTrait;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use App\Entity\Traits\DurationSupportTrait;
use App\Entity\Traits\GeoLocationEntityTrait;
use App\Entity\Traits\LocationEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    use ArchivedTrait;
    use CreatedUpdatedEntityTrait;
    use DurationSupportTrait;
    use GeoLocationEntityTrait;
    use LocationEntityTrait;
    use SoftDeleteableEntity;

    public const CACHE_SUPPORT_KEY = 'support_group';
    public const CACHE_FULLSUPPORT_KEY = 'support_group_full';
    public const CACHE_SUPPORT_NOTES_KEY = 'support.notes';
    public const CACHE_SUPPORT_RDVS_KEY = 'support.rdvs';
    public const CACHE_SUPPORT_DOCUMENTS_KEY = 'support.documents';
    public const CACHE_SUPPORT_PAYMENTS_KEY = 'support.payments';
    public const CACHE_SUPPORT_NB_NOTES_KEY = 'support.notes_count';
    public const CACHE_SUPPORT_NB_RDVS_KEY = 'support.rdvs_count';
    public const CACHE_SUPPORT_NB_DOCUMENTS_KEY = 'support.documents_count';
    public const CACHE_SUPPORT_NB_PAYMENTS_KEY = 'support.payments_count';
    public const CACHE_SUPPORT_LAST_RDV_KEY = 'support.last_rdv';
    public const CACHE_SUPPORT_NEXT_RDV_KEY = 'support.next_rdv';
    public const CACHE_SUPPORT_TASKS_KEY = 'support.tasks';
    public const CACHE_SUPPORT_NB_TASKS_KEY = 'support.tasks_count';

    public const STATUS_IN_PROGRESS = 2;
    public const STATUS_ENDED = 4;
    public const STATUS_PRE_ADD_IN_PROGRESS = 1;
    public const STATUS_PRE_ADD_FAILED = 5;
    public const STATUS_WAITING_LIST = 6;
    public const STATUS_SUSPENDED = 3;
    public const STATUS_OTHER = 97;

    public const STATUS = [
        2 => 'En cours',
        4 => 'Termin??',
        1 => 'Pr??-admission en cours',
        5 => 'Pr??-admission non aboutie',
        6 => 'Liste d\'attente',
        3 => 'Suspendu',
        97 => 'Autre',
    ];

    public const END_REASONS = [
        100 => 'Acc??s ?? une solution d\'h??bgt/logt',
        110 => 'Autonome', // AVDL
        120 => 'Objectif r??alis??',
        200 => 'Non adh??sion ?? l\'accompagnement', // 2
        210 => 'Exclusion disciplinaire',
        220 => 'Fin du contrat de s??jour',
        500 => 'Fin d\'intervention d\'urgence', // PASH 6
        510 => 'Fin de prise en charge 115', // PASH  5
        520 => 'Fin de prise en charge ASE', // PASH 3
        400 => 'Fin de prise en charge OFII', // Asile
        410 => 'Transfert Dublin', // Asile
        300 => 'D??part vers un autre d??partement', // 4
        310 => 'D??part volontaire',
        230 => 'Retour dans le pays d\'origine',
        240 => 'S??paration du couple',
        330 => 'Transfert vers autre d??partement', // AVDL
        900 => 'D??c??s',
        97 => 'Autre',
        99 => 'Inconnu',
    ];

    public const REGULAR_END_REASONS = [
        100 => 'Acc??s ?? une solution d\'h??bgt/logt',
        120 => 'Objectif r??alis??',
        200 => 'Non adh??sion ?? l\'accompagnement',
        210 => 'Exclusion disciplinaire',
        220 => 'Fin du contrat de s??jour',
        300 => 'D??part vers un autre d??partement',
        310 => 'D??part volontaire',
        230 => 'Retour dans le pays d\'origine',
        240 => 'S??paration du couple',
        900 => 'D??c??s',
        97 => 'Autre',
        99 => 'Inconnu',
    ];

    public const END_STATUS = [
        001 => 'A la rue - abri de fortune',
        303 => 'Acc??s ?? la propri??t??',
        208 => 'ALTHO',
        400 => 'CADA - dispositif asile',
        304 => 'Colocation',
        500 => 'D??tention',
        105 => 'Dispositif hivernal',
        502 => 'DLSAP',
        106 => 'Foyer maternel',
        010 => 'H??berg?? chez des tiers',
        011 => 'H??berg?? chez famille',
        100 => 'H??tel 115',
        101 => 'H??tel (hors 115)',
        102 => 'H??bergement d???urgence',
        103 => 'H??bergement de stabilisation',
        104 => 'H??bergement d???insertion',
        600 => 'H??pital',
        401 => 'HUDA',
        601 => 'LHSS',
        200 => 'Logement adapt?? - ALT',
        201 => 'Logement adapt?? - FJT',
        202 => 'Logement adapt?? - FTM',
        203 => 'Logement adapt?? - Maison relais',
        204 => 'Logement adapt?? - R??sidence sociale',
        205 => 'Logement adapt?? - RHVS',
        206 => 'Logement adapt?? - Solibail/IML',
        207 => 'Logement foyer',
        300 => 'Logement priv??',
        301 => 'Logement social',
        305 => 'Maison de retraite',
        501 => 'Placement ext??rieur',
        302 => 'Sous-location',
        002 => 'Squat',
        602 => 'Structure de soin ou m??dical (LAM, autre)',
        97 => 'Autre',
        99 => 'Inconnue',
    ];

    public const DEFAULT_COEFFICIENT = 1;
    public const COEFFICIENT_DOUBLE = 2;
    public const COEFFICIENT_HALF = 0.5;
    public const COEFFICIENT_THIRD = 0.3;
    public const COEFFICIENT_QUARTER = 0.25;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups("show_support_group")
     */
    private $id;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $startDate;

    /**
     * @ORM\Column(type="date", nullable=true)
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
     * @Assert\NotNull(message="Le statut doit ??tre renseign??.")
     */
    private $status = 2;

    /**
     * @Groups("export")
     */
    private $statusToString;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\User", inversedBy="referentSupports")
     * @Groups("export")
     */
    private $referent;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\User", inversedBy="referent2Supports")
     * @Groups("export")
     */
    private $referent2;

    /**
     * @ORM\Column(type="float", nullable=true, options={"default":1})
     * @Assert\Range(min = 0, max = 10,
     * minMessage="Le coefficient ne peut ??tre inf??rieur ?? {{ limit }}",
     * maxMessage="Le coefficient ne peut ??tre sup??rieur ?? {{ limit }}")
     * @Groups("export")
     */
    private $coefficient = self::DEFAULT_COEFFICIENT;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $endReason;

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

    private $endLocationFullAddress;

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
     * @ORM\Column(type="float", nullable=true)
     */
    private $evaluationScore;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $siSiaoId;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $nbChildrenUnder3years;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups("view")
     */
    private $comment;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\User", inversedBy="createdSupports")
     * @MaxDepth(1)
     */
    protected $createdBy; // NE PAS SUPPRIMER

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\People\PeopleGroup", inversedBy="supports")
     * @ORM\JoinColumn(nullable=false)
     * @MaxDepth(1)
     */
    private $peopleGroup;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Support\SupportPerson", mappedBy="supportGroup", cascade={"persist", "remove"}, orphanRemoval=true)
     * @Groups("show_support_person")
     * @MaxDepth(1)
     */
    private $supportPeople;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization\Service", inversedBy="supportGroup")
     * @Groups({"export", "show_service"})
     */
    private $service;

    /**
     * @ORM\ManyToOne(targetEntity=SubService::class, inversedBy="supportGroups")
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
     * @ORM\OneToMany(targetEntity=Rdv::class, mappedBy="supportGroup", cascade={"persist", "remove"})
     * @MaxDepth(1)
     */
    private $rdvs;

    /**
     * @var Collection<Task>
     * @ORM\OneToMany(targetEntity=Task::class, mappedBy="supportGroup", cascade={"remove"})
     * @MaxDepth(1)
     */
    private $tasks;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Support\Document", mappedBy="supportGroup", cascade={"persist", "remove"})
     * @MaxDepth(1)
     */
    private $documents;

    /**
     * @ORM\OneToMany(targetEntity=Payment::class, mappedBy="supportGroup", orphanRemoval=true, cascade={"persist", "remove"})
     */
    private $payments;

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
     * @ORM\OneToOne(targetEntity=OriginRequest::class, inversedBy="supportGroup", cascade={"persist", "remove"})
     */
    private $originRequest;

    /**
     * @ORM\OneToOne(targetEntity=EvalInitGroup::class, inversedBy="supportGroup", cascade={"persist", "remove"})
     */
    private $evalInitGroup;

    /**
     * @ORM\OneToOne(targetEntity=Avdl::class, inversedBy="supportGroup", cascade={"persist", "remove"})
     */
    private $avdl;

    /**
     * @ORM\OneToOne(targetEntity=HotelSupport::class, inversedBy="supportGroup", cascade={"persist", "remove"})
     */
    private $hotelSupport;

    public function __construct()
    {
        $this->supportPeople = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->rdvs = new ArrayCollection();
        $this->tasks = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->placeGroups = new ArrayCollection();
        $this->evaluationsGroup = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->id;
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
        return $this->endReason ? self::END_REASONS[$this->endReason] : null;
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

    public function getEndLocationFullAddress(): ?string
    {
        if (null === $this->endLocationCity) {
            return null;
        }

        return $this->endLocationAddress.', '.$this->endLocationZipcode
            .($this->endLocationCity !== $this->endLocationAddress ? ' '.$this->endLocationCity : '');
    }

    public function setEndLocationFullAddress(?string $endLocationFullAddress): self
    {
        $this->endLocationFullAddress = $endLocationFullAddress;

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

    public function getEndLocationDept(): ?string
    {
        return $this->endLocationZipcode ? substr($this->endLocationZipcode, 0, 2) : null;
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

    public function getEvaluationScore(): ?float
    {
        return $this->evaluationScore;
    }

    public function setEvaluationScore(?float $evaluationScore): self
    {
        $this->evaluationScore = $evaluationScore;

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
     * @return Collection<SupportPerson>|null
     */
    public function getSupportPeople(): ?Collection
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

    /**
     * @Groups({"export", "show_service"})
     */
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
     * @return Collection<Note>|null
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
     * @return Collection<Rdv>|null
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
     * @return Collection<Task>|null
     */
    public function getTasks(): ?Collection
    {
        return $this->tasks;
    }

    public function countActiveTasks(): ?int
    {
        $count = 0;

        foreach ($this->tasks as $task) {
            if (Task::TASK_IS_NOT_DONE === $task->getStatus()) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @return Collection<Document>|null
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
     * @return Collection<PlaceGroup>|null
     */
    public function getPlaceGroups(): ?Collection
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
     * @return Collection<EvaluationGroup>|null
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

    public function getEvalInitGroup(): ?EvalInitGroup
    {
        return $this->evalInitGroup;
    }

    public function setEvalInitGroup(?EvalInitGroup $evalInitGroup): self
    {
        $this->evalInitGroup = $evalInitGroup;

        return $this;
    }

    public function getOriginRequest(): ?OriginRequest
    {
        return $this->originRequest;
    }

    public function setOriginRequest(?OriginRequest $originRequest): self
    {
        if ($originRequest->getId() || true === (bool) array_filter((array) $originRequest)) {
            $this->originRequest = $originRequest;
        }

        return $this;
    }

    /**
     * @return Collection<Payment>|null
     */
    public function getPayments(): ?Collection
    {
        return $this->payments;
    }

    public function addPayment(Payment $payment): self
    {
        if (!$this->payments->contains($payment)) {
            $this->payments[] = $payment;
            $payment->setSupportGroup($this);
        }

        return $this;
    }

    public function removePayment(Payment $payment): self
    {
        if ($this->payments->contains($payment)) {
            $this->payments->removeElement($payment);
            // set the owning side to null (unless already changed)
            if ($payment->getSupportGroup() === $this) {
                $payment->setSupportGroup(null);
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

        return $this;
    }

    public function getHotelSupport(): ?HotelSupport
    {
        return $this->hotelSupport;
    }

    public function setHotelSupport(HotelSupport $hotelSupport): self
    {
        $this->hotelSupport = $hotelSupport;

        return $this;
    }

    /**
     * Donne le demandeur principal du suivi.
     *
     * @Groups("show_support_group")
     */
    public function getHeader(): ?Person
    {
        foreach ($this->getSupportPeople() as $supportPerson) {
            if (true === $supportPerson->getHead()) {
                return $supportPerson->getPerson();
            }
        }

        if ($supportPerson = $this->getSupportPeople()->first()) {
            return $supportPerson->getPerson();
        }

        return null;
    }
}
