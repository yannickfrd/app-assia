<?php

namespace App\Form\Model\Event;

use App\Entity\Support\SupportGroup;
use App\Form\Model\Organization\ReferentServiceDeviceSearchTrait;
use App\Form\Model\Traits\DateSearchTrait;
use Doctrine\Common\Collections\ArrayCollection;

class EventSearch
{
    use ReferentServiceDeviceSearchTrait;
    use DateSearchTrait;

    /** @var int */
    protected $id;

    /** @var string|null */
    protected $title;

    /** @var array */
    protected $types;

    /** @var array */
    protected $status;

    /** @var SupportGroup */
    protected $supportGroup;

    /** @var ArrayCollection|null */
    protected $users;

    /** @var ArrayCollection|null */
    protected $tags;

    /** @var string|null */
    protected $location;

    /** @var string|null */
    protected $fullname;

    /** @var bool */
    protected $export;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
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

    public function getStatus(): array
    {
        return $this->status;
    }

    public function setStatus(array $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getTypes(): array
    {
        return $this->types;
    }

    public function setTypes(array $types): self
    {
        $this->types = $types;

        return $this;
    }

    public function getUsers(): ?ArrayCollection
    {
        return $this->users;
    }

    public function setUsers(?ArrayCollection $users): self
    {
        $this->users = $users;

        return $this;
    }

    public function getSupportGroup(): ?SupportGroup
    {
        return $this->supportGroup;
    }

    public function setSupportGroup(SupportGroup $supportGroup): self
    {
        $this->supportGroup = $supportGroup;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getTags(): ?ArrayCollection
    {
        return $this->tags;
    }

    public function setTags(?ArrayCollection $tags): self
    {
        $this->tags = $tags;

        return $this;
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): self
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getExport(): ?bool
    {
        return $this->export;
    }

    public function setExport(bool $export): self
    {
        $this->export = $export;

        return $this;
    }
}
