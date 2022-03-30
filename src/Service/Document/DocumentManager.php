<?php

namespace App\Service\Document;

use App\Entity\Support\SupportGroup;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class DocumentManager
{
    public static function deleteCacheItems(SupportGroup $supportGroup, $isUpdate = false): void
    {
        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        if (false === $isUpdate) {
            $cache->deleteItem(SupportGroup::CACHE_SUPPORT_NB_DOCUMENTS_KEY.$supportGroup->getId());
        }

        $cache->deleteItem(SupportGroup::CACHE_SUPPORT_DOCUMENTS_KEY.$supportGroup->getId());
    }
}
