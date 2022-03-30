<?php

namespace App\Service\Note;

use App\Entity\Organization\User;
use App\Entity\Support\Note;
use App\Entity\Support\SupportGroup;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class NoteManager
{
    public static function deleteCacheItems(Note $note): void
    {
        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
        $supportGroup = $note->getSupportGroup();

        $cache->deleteItems([
            User::CACHE_USER_NOTES_KEY.$note->getCreatedBy()->getId(),
            SupportGroup::CACHE_SUPPORT_NOTES_KEY.$supportGroup->getId(),
            SupportGroup::CACHE_SUPPORT_NB_NOTES_KEY.$supportGroup->getId(),
        ]);
    }
}
