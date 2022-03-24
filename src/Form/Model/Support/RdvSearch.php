<?php

namespace App\Form\Model\Support;

use App\Form\Model\Traits\DateSearchTrait;
use Doctrine\Common\Collections\ArrayCollection;
use App\Form\Model\Support\Traits\RdvSearchTrait;
use App\Form\Model\Organization\ReferentServiceDeviceSearchTrait;

class RdvSearch
{
    use RdvSearchTrait;
    use ReferentServiceDeviceSearchTrait;
    use DateSearchTrait;

    /** @var int */
    private $id;

    /** @var string|null */
    private $fullname;

    /** @var bool */
    private $export;

    /** @var ArrayCollection|null */
    protected $users;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

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

    public function getUsers(): ?ArrayCollection
    {
        return $this->users;
    }

    public function setUsers(?ArrayCollection $users): self
    {
        $this->users = $users;

        return $this;
    }
}
