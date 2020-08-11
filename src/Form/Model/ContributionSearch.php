<?php

namespace App\Form\Model;

use App\Form\Model\Traits\DateSearchTrait;
use App\Form\Model\Traits\ContributionSearchTrait;
use App\Form\Model\Traits\ReferentServiceDeviceSearchTrait;

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
     * @var string|null
     */
    private $fullname;

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
