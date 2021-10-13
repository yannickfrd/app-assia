<?php

namespace App\Event\People;

use App\Entity\People\PeopleGroup;
use App\Entity\Support\SupportGroup;
use Doctrine\Common\Collections\Collection;
use Symfony\Contracts\EventDispatcher\Event;

class PeopleGroupEvent extends Event
{
    public const NAME = 'people_group.event';

    private $peopleGroup;
    private $supports;

    /**
     * @param Collection<SupportGroup>|null $supports
     */
    public function __construct(PeopleGroup $peopleGroup, $supports = null)
    {
        $this->peopleGroup = $peopleGroup;
        $this->supports = $supports;
    }

    public function getPeopleGroup(): PeopleGroup
    {
        return $this->peopleGroup;
    }

    /**
     * @return Collection<SupportGroup>|null
     */
    public function getSupports()
    {
        return $this->supports;
    }
}
