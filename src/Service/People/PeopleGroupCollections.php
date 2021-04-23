<?php

namespace App\Service\People;

use App\Entity\People\PeopleGroup;
use App\Repository\Organization\ReferentRepository;
use App\Repository\Support\SupportGroupRepository;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class PeopleGroupCollections
{
    private $supportRepo;
    private $referentRepo;
    private $cache;

    public function __construct(SupportGroupRepository $supportRepo, ReferentRepository $referentRepo)
    {
        $this->supportRepo = $supportRepo;
        $this->referentRepo = $referentRepo;
        $this->cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
    }

    public function getSupports(PeopleGroup $peopleGroup)
    {
        return $this->cache->get(PeopleGroup::CACHE_GROUP_SUPPORTS_KEY.$peopleGroup->getId(), function (CacheItemInterface $item) use ($peopleGroup) {
            $item->expiresAfter(\DateInterval::createFromDateString('30 days'));

            return $this->supportRepo->findSupportsOfPeopleGroup($peopleGroup);
        });
    }

    public function getReferents(PeopleGroup $peopleGroup)
    {
        return $this->cache->get(PeopleGroup::CACHE_GROUP_REFERENTS_KEY.$peopleGroup->getId(), function (CacheItemInterface $item) use ($peopleGroup) {
            $item->expiresAfter(\DateInterval::createFromDateString('30 days'));

            return $this->referentRepo->findReferentsOfPeopleGroup($peopleGroup);
        });
    }
}
