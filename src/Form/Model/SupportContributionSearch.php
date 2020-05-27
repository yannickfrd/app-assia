<?php

namespace App\Form\Model;

use App\Form\Model\Traits\ContributionSearchTrait;
use App\Form\Model\Traits\DateSearchTrait;

class SupportContributionSearch
{
    use ContributionSearchTrait;
    use DateSearchTrait;

    /**
     * @var int|null
     */
    private $type;

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }
}
