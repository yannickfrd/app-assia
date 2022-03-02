<?php

namespace App\Service\Place;

use App\Entity\Organization\Place;
use App\Entity\Organization\Service;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class PlaceManager
{
    public static function deleteCacheItems(Place $place): bool
    {
        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        return $cache->deleteItem(Service::CACHE_SERVICE_PLACES_KEY.$place->getService()->getId());
    }
}
