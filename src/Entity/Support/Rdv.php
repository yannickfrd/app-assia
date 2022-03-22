<?php

namespace App\Entity\Support;

use App\Entity\Event\AbstractEvent;
use App\Entity\Event\Alert;
use App\Entity\Organization\TagTrait;
use App\Entity\Organization\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Support\RdvRepository")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false, hardDelete=true)
 */
class Rdv extends AbstractEvent
{
    use TagTrait;
    use SoftDeleteableEntity;

    public const SERIALIZER_GROUPS = [
        'show_rdv', 'show_tag', 'show_support_group', 'show_alert',
    ];

    public const TYPE_EVENT = 0;

    public const NO_STATUS = 99;

    public const STATUS = [
        1 => 'Présent',
        2 => 'Absent',
        3 => 'Annulé',
        99 => 'Non renseigné',
    ];

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotNull()
     * @Groups("show_rdv")
     */
    protected $start;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups("show_rdv")
     */
    private $status;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="rdvsCreated")
     * @Groups("show_rdv")
     */
    protected $createdBy; // NE PAS SUPPRIMER

    /**
     * @var Collection<User>
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="rdvs")
     * @ORM\OrderBy({"lastname": "ASC"})
     * @Groups("show_rdv")
     */
    protected $users;

    /**
     * @var SupportGroup
     * @ORM\ManyToOne(targetEntity=SupportGroup::class, inversedBy="rdvs", cascade={"persist"})
     * @Groups("show_rdv")
     */
    private $supportGroup;

//    /**
//     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="rdvs")
//     */
//    private $user;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Groups("show_rdv")
     */
    private $googleEventId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups("show_rdv")
     */
    private $outlookEventId;

    /**
     * @var Collection<Alert>
     * @ORM\OneToMany(targetEntity=Alert::class, mappedBy="rdv", orphanRemoval=true, cascade={"persist"})
     * @ORM\JoinColumn(name="alert", nullable=true)
     * @ORM\OrderBy({"date": "ASC"})
     * @Groups("show_rdv")
     */
    protected $alerts;

    public function __construct()
    {
        parent::__construct();

        $this->users = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function addAlert(?Alert $alert): self
    {
        if (!$this->alerts->contains($alert)) {
            $alert->setRdv($this);
            $this->alerts[] = $alert;
        }

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /** @Groups("show_rdv") */
    public function getStatusToString(): ?string
    {
        return $this->status ? self::STATUS[$this->status] : null;
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

//    public function getUser(): ?User
//    {
//        return $this->user;
//    }
//
//    public function setUser(?User $user): self
//    {
//        $this->user = $user;
//
//        return $this;
//    }

    public function getGoogleEventId(): ?string
    {
        return $this->googleEventId;
    }

    public function setGoogleEventId(?string $googleEventId): self
    {
        $this->googleEventId = $googleEventId;

        return $this;
    }

    public function getOutlookEventId(): ?string
    {
        return $this->outlookEventId;
    }

    public function setOutlookEventId(?string $outlookEventId): self
    {
        $this->outlookEventId = $outlookEventId;

        return $this;
    }
}
