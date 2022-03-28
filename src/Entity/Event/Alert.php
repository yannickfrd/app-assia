<?php

namespace App\Entity\Event;

use App\Repository\Event\AlertRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=AlertRepository::class)
 */
class Alert
{
    public const EMAIL_TYPE = 1;
    public const NOTIFICATION_TYPE = 0;

    public const TYPE = [
        1 => 'Email',
        // 0 => 'Notification',
    ];

    public const IS_SENDED = [
        false => 'En attente',
        true => 'Envoyé',
    ];

    public const IS_VIEWED = [
        false => 'Non-vue',
        true => 'Vue',
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("show_alert")
     */
    private $id;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups("show_alert")
     */
    private $type = self::EMAIL_TYPE;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("show_alert")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity=Task::class, inversedBy="alerts")
     */
    private $task;

    /**
     * @ORM\ManyToOne(targetEntity=Rdv::class, inversedBy="alerts")
     */
    private $rdv;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("show_alert")
     */
    private $sended = false;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("show_alert")
     */
    private $viewed = false;

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function getTypeToString(): ?string
    {
        return self::TYPE[$this->type];
    }

    public function setType(?int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    /**
     * @Groups("show_alert")
     */
    public function getDateToString(string $format = 'd/m/Y H:i'): string
    {
        return $this->date ? $this->date->format($format) : '';
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function setTask(?Task $task): self
    {
        $this->task = $task;

        return $this;
    }

    public function getTask(): Task
    {
        return $this->task;
    }

    public function getRdv(): Rdv
    {
        return $this->rdv;
    }

    public function setRdv(Rdv $rdv): self
    {
        $this->rdv = $rdv;

        return $this;
    }

    public function getViewed(): ?bool
    {
        return $this->viewed;
    }

    public function setViewed(bool $viewed): self
    {
        $this->viewed = $viewed;

        return $this;
    }

    public function getViewedToString(): string
    {
        return self::IS_VIEWED[$this->viewed];
    }

    public function getSended(): ?bool
    {
        return $this->sended;
    }

    public function setSended(bool $sended): self
    {
        $this->sended = $sended;

        return $this;
    }

    public function getSendedToString(): string
    {
        return self::IS_SENDED[$this->sended];
    }
}
