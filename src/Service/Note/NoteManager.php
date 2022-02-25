<?php

namespace App\Service\Note;

use App\Entity\Organization\User;
use App\Entity\Support\Note;
use App\Entity\Support\SupportGroup;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class NoteManager
{
    public function deleteCacheItems(Note $note): void
    {
        $supportGroup = $note->getSupportGroup();
        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        if (null === $note->getId() || $note->getCreatedAt()->format('U') === $note->getUpdatedAt()->format('U')) {
            $cache->deleteItem(SupportGroup::CACHE_SUPPORT_NB_NOTES_KEY.$supportGroup->getId());
        }

        $cache->deleteItems([
            SupportGroup::CACHE_SUPPORT_NOTES_KEY.$supportGroup->getId(),
            User::CACHE_USER_NOTES_KEY.$note->getCreatedBy()->getId(),
        ]);
    }
}
