<?php

namespace App\Form\Model\Support;

use App\Form\Model\Organization\ReferentServiceDeviceSearchTrait;
use App\Form\Model\Support\Traits\NoteSearchTrait;
use App\Form\Model\Traits\DateSearchTrait;

class NoteSearch
{
    use NoteSearchTrait;
    use DateSearchTrait;
    use ReferentServiceDeviceSearchTrait;

    /** @var int */
    private $id;

    /** @var string|null */
    private $fullname;

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
}
