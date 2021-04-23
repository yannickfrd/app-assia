<?php

namespace App\Service\SupportGroup;

use App\Entity\Support\SupportGroup;
use App\Repository\Support\SupportGroupRepository;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class SupportManager
{
    private $supportGroupRepo;
    private $cache;

    public function __construct(SupportGroupRepository $supportGroupRepo)
    {
        $this->supportGroupRepo = $supportGroupRepo;
        $this->cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
    }

    /**
     * Donne le suivi social complet.
     */
    public function getFullSupportGroup(int $id): ?SupportGroup
    {
        $supportGroup = $this->cache->get(SupportGroup::CACHE_FULLSUPPORT_KEY.$id, function (CacheItemInterface $item) use ($id) {
            $item->expiresAfter(\DateInterval::createFromDateString('7 days'));

            return $this->supportGroupRepo->findFullSupportById($id);
        });

        return $supportGroup;
    }

    /**
     * Donne le suivi social.
     */
    public function getSupportGroup(int $id): ?SupportGroup
    {
        return $this->cache->get(SupportGroup::CACHE_SUPPORT_KEY.$id, function (CacheItemInterface $item) use ($id) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 month'));

            return $this->supportGroupRepo->findSupportById($id);
        });
    }
}
