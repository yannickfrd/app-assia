<?php

namespace App\Form\Model\Support;

use App\Form\Model\Support\Traits\ContributionSearchTrait;
use App\Form\Model\Traits\DateSearchTrait;

class SupportContributionSearch
{
    use ContributionSearchTrait;
    use DateSearchTrait;

    /** @var int|null */
    private $contributionId;

    public function getContributionId(): ?int
    {
        return $this->contributionId;
    }

    public function setContributionId(int $contributionId): self
    {
        $this->contributionId = $contributionId;

        return $this;
    }
}
