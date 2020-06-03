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
    private $contributionId;

    /**
     * @var int|null
     */
    private $type;

    public function getContributionId(): ?int
    {
        return $this->contributionId;
    }

    public function setContributionId(int $contributionId): self
    {
        $this->contributionId = $contributionId;

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
}
