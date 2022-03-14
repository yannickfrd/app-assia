<?php

namespace App\Entity\Event;

use App\Entity\Organization\TagTrait;
use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use App\Repository\Event\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
     * @ORM\Column(type="smallint")
     * @Groups("show_event")
     */
    protected $type = parent::TYPE_TASK;

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
     * @ORM\OrderBy({"lastname": "ASC"})
     * @Groups("show_event")
     */
    protected $users;

    /**
     * @var SupportGroup
     * @ORM\ManyToOne(targetEntity=SupportGroup::class, inversedBy="tasks")
     * @Groups("show_support_group")
     */
    protected $supportGroup;

    // /**
    //  * @var Collection<SupportPerson>
    //  * @ORM\ManyToMany(targetEntity=SupportPerson::class, inversedBy="tasks", fetch="EXTRA_LAZY")
    //  * @Groups("show_support_person")
    //  */
    // protected $supportPeople;

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

        $this->users = new ArrayCollection();
        $this->tags = new ArrayCollection();

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

    public function getSupportGroup(): ?SupportGroup
    {
        return $this->supportGroup;
    }

    public function setSupportGroup(?SupportGroup $supportGroup): self
    {
        $this->supportGroup = $supportGroup;

        return $this;
    }


//    /**
//     * @return Collection<User>|User[]|null
//     */
//    public function getUsers(): ?Collection
//    {
//        return $this->users;
//    }
//
//    /** @Groups("show_event") */
//    public function getUsersToString(): string
//    {
//        $userNames = [];
//
//        foreach ($this->users as $user) {
//            $userNames[] = $user->getFullname();
//        }
//
//        return join(', ', $userNames);
//    }
//
//    public function addUser(?User $user): self
//    {
//        if (!$this->users->contains($user)) {
//            $this->users[] = $user;
//        }
//
//        return $this;
//    }
//
//    public function removeUser(User $user): self
//    {
//        $this->users->removeElement($user);
//
//        return $this;
//    }

}
