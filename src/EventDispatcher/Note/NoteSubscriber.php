<?php

namespace App\EventDispatcher\Note;

use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use App\Event\Note\NoteEvent;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NoteSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'note.after_create' => 'discache',
            'note.after_update' => 'discache',
        ];
    }

    /**
     * Supprime les rendez-vous en cache du suivi et de l'utlisateur.
     */
    public function discache(NoteEvent $event): bool
    {
        $note = $event->getNote();
        $supportGroup = $note->getSupportGroup();
        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        if (null === $note->getId() || $note->getCreatedAt()->format('U') === $note->getUpdatedAt()->format('U')) {
            $cache->deleteItem(SupportGroup::CACHE_SUPPORT_NB_NOTES_KEY.$supportGroup->getId());
        }

        return $cache->deleteItems([
            SupportGroup::CACHE_SUPPORT_NOTES_KEY.$supportGroup->getId(),
            User::CACHE_USER_NOTES_KEY.$note->getCreatedBy()->getId(),
        ]);
    }
}
