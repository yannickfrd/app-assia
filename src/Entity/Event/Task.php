<?php

namespace App\Entity\Event;

use App\Entity\Organization\TagTrait;
use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use App\Repository\Event\TaskRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 */
class Task extends AbstractEvent
{
    use TagTrait;

    public const TASK_IS_DONE = true;
    public const TASK_IS_NOT_DONE = false;

    public const STATUS = [
        false => 'Non réalisée',
        true => 'Réalisée',
    ];

    public const LEVEL = [
        1 => 'Faible',
        2 => 'Moyenne',
        3 => 'Élevée',
    ];

    public const MEDIUM_LEVEL = 2;

    public const SERIALIZER_GROUPS = [
        'show_event', 'show_support_group', 'show_support_person', 'show_person',
        'show_user', 'show_service', 'show_alert', 'show_tag',
    ];

    /**
     * @ORM\Column(type="boolean")
     * @Groups("show_event")
     */
    protected $status = self::TASK_IS_NOT_DONE;

    /**
     * @ORM\Column(type="smallint")
     * @Groups("show_event")
     */
    private $level = self::MEDIUM_LEVEL;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $autoTaskId;

    /**
     * @var Collection<User>
     * @ORM\ManyToMany(targetEntity=User::class, inversedBy="tasks")
     * @Groups("show_user")
     * @ORM\OrderBy({"lastname": "ASC"})
     */
    protected $users;

    /**
     * @var SupportGroup
     * @ORM\ManyToOne(targetEntity=SupportGroup::class, inversedBy="tasks")
     * @Groups({"show_support_group"})
     */
    protected $supportGroup;

    /**
     * @var Collection<SupportPerson>
     * @ORM\ManyToMany(targetEntity=SupportPerson::class, inversedBy="tasks", fetch="EXTRA_LAZY")
     * @Groups("show_support_person")
     */
    protected $supportPeople;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="tasksCreated")
     * @Groups("show_user")
     * @MaxDepth(1)
     */
    protected $createdBy; // NE PAS SUPPRIMER

    public function __construct()
    {
        parent::__construct();
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function toggleStatus(): bool
    {
        return $this->status = $this->status ? false : true;
    }

    public function getRdvStatus(): ?int
    {
        return $this->rdvStatus;
    }

    /** @Groups("show_event") */
    public function getStatusToString(): string
    {
        return self::STATUS[$this->status];
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    /** @Groups("show_event") */
    public function getLevelToString(): ?string
    {
        return false !== $this->level ? self::LEVEL[$this->level] : null;
    }

    public function getAutoTaskId(): string
    {
        return $this->autoTaskId;
    }

    public function setAutoTaskId($autoTaskId): self
    {
        $this->autoTaskId = $autoTaskId;

        return $this;
    }

    public function addAlert(?Alert $alert): self
    {
        if (!$this->alerts->contains($alert)) {
            $alert->setTask($this);
            $this->alerts[] = $alert;
        }

        return $this;
    }
}
