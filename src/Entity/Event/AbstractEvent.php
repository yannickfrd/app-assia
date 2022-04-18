<?php

namespace App\Entity\Event;

use App\Entity\Organization\User;
use App\Entity\Traits\CreatedUpdatedEntityTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractEvent
{
    use CreatedUpdatedEntityTrait;
    use SoftDeleteableEntity;

    public const TYPE = [
        0 => 'Rendez-vous',
        1 => 'Tâche',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"show_event", "show_rdv"})
     */
    protected $id;

    /**
     * @ORM\Column(type="smallint")
     * @Groups("show_event")
     */
    protected $type;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"show_event", "show_rdv"})
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @ORM\Column(type="datetime",  nullable=true)
     */
    protected $start;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotNull()
     * @Groups({"show_event", "show_rdv"})
     */
    protected $end;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"show_event", "show_rdv"})
     */
    protected $content;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"show_event", "show_rdv"})
     */
    protected $location;

    /**
     * @Gedmo\Blameable(on="create", on="update")
     * @ORM\ManyToOne(targetEntity=User::class)
     * @Groups({"show_user", "show_rdv"})
     * @MaxDepth(1)
     */
    protected $updatedBy; // NE PAS SUPPRIMER

    public function __construct()
    {
        $this->type = get_class($this)::TYPE_EVENT;
        $this->alerts = new ArrayCollection();
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function getStartToString(string $format = 'd/m/Y H:i'): ?string
    {
        return $this->start ? $this->start->format($format) : '';
    }

    public function setStart(?\DateTimeInterface $start): self
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    /**
     * @Groups("show_event")
     */
    public function getEndToString(string $format = 'd/m/Y H:i'): ?string
    {
        if ('00:00' === $this->end->format('H:i')) {
            return $this->end->format('d/m/Y');
        }

        return $this->end->format($format);
    }

    public function setEnd(?\DateTimeInterface $end): self
    {
        $this->end = $end;

        return $this;
    }

    /**
     * @Groups("show_event")
     */
    public function getTypeToString(): ?string
    {
        return $this->type ? self::TYPE[$this->type] : null;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content ?? '';

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location ?? '';

        return $this;
    }

    /**
     * @return Collection<User>|null
     */
    public function getUsers(): ?Collection
    {
        return $this->users;
    }

    /** @Groups({"show_event", "show_rdv"}) */
    public function getUsersToString(): string
    {
        if (null === $this->users) {
            return '';
        }

        $userNames = [];

        foreach ($this->users as $user) {
            $userNames[] = $user->getFullname();
        }

        return join(', ', $userNames);
    }

    public function addUser(?User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->users->removeElement($user);

        return $this;
    }

    /**
     * @return Collection<Alert>|null
     */
    public function getAlerts(): ?Collection
    {
        return $this->alerts;
    }

    public function removeAlert(Alert $alert): self
    {
        $this->alerts->removeElement($alert);

        return $this;
    }

    public function countActiveAlerts(): int
    {
        $countActiveAlerts = 0;

        foreach ($this->alerts as $alert) {
            if (Alert::NOTIFICATION_TYPE === $alert->getType() && false === $alert->getViewed()) {
                ++$countActiveAlerts;
            }
        }

        return $countActiveAlerts;
    }

    public function countViewedAlerts(): int
    {
        $countViewedAlerts = 0;

        foreach ($this->alerts as $alert) {
            if (Alert::NOTIFICATION_TYPE === $alert->getType() && true === $alert->getViewed()) {
                ++$countViewedAlerts;
            }
        }

        return $countViewedAlerts;
    }

    /** @Groups({"show_event", "show_rdv"}) */
    public function getCreatedAtToString(string $format = 'd/m/Y H:i'): string
    {
        return $this->createdAt ? $this->createdAt->format($format) : '';
    }

    /** @Groups({"show_event", "show_rdv"}) */
    public function getUpdatedAtToString(string $format = 'd/m/Y H:i'): string
    {
        return $this->updatedAt ? $this->updatedAt->format($format) : '';
    }

    // /**
    //  * @return Collection<SupportPerson>|null
    //  */
    // public function getSupportPeople(): ?Collection
    // {
    //     return $this->supportPeople;
    // }

    // public function addSupportPerson(SupportPerson $supportPerson): self
    // {
    //     if (!$this->supportPeople->contains($supportPerson)) {
    //         $this->supportPeople[] = $supportPerson;
    //     }

    //     return $this;
    // }

    // public function setSupportPeople(?Collection $supportPeople): self
    // {
    //     $this->supportPeople = $supportPeople;

    //     return $this;
    // }

    // public function removeSupportPerson(SupportPerson $supportPerson): self
    // {
    //     $this->supportPeople->removeElement($supportPerson);

    //     return $this;
    // }
}
