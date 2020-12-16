<?php

namespace App\Form\Model\Support;

use App\Form\Model\Organization\ReferentServiceDeviceSearchTrait;
use App\Form\Model\Support\Traits\ContributionSearchTrait;
use App\Form\Model\Traits\DateSearchTrait;

class ContributionSearch
{
    use ContributionSearchTrait;
    use DateSearchTrait;
    use ReferentServiceDeviceSearchTrait;

    public const DATE_TYPE = [
        1 => 'Date de l\'opération',
        2 => 'Période de la redevance',
        3 => 'Date de création',
    ];

    public const DATE_TYPE_DEFAULT = 1;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string|null
     */
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
