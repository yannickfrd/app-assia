<?php

namespace App\Event\Support;

use App\Entity\Support\Contribution;
use App\Entity\Support\SupportGroup;
use Symfony\Contracts\EventDispatcher\Event;

class ContributionEvent extends Event
{
    public const NAME = 'contribution.event';

    private $contribution;
    private $supportGroup;

    public function __construct(Contribution $contribution, SupportGroup $supportGroup)
    {
        $this->contribution = $contribution;
        $this->supportGroup = $supportGroup;
    }

    public function getContribution(): Contribution
    {
        return $this->contribution;
    }
    public function getSupportGroup(): SupportGroup
    {
        return $this->supportGroup;
    }

    
}
