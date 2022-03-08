<?php

namespace App\EventDispatcher\Rdv;

use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use App\Event\Rdv\RdvEvent;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RdvSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'rdv.before_create' => 'dispatch',
            'rdv.before_update' => 'dispatch',
            'rdv.after_create' => 'discache',
            'rdv.after_update' => 'discache',
        ];
    }

    public function dispatch(RdvEvent $event)
    {
        $rdv = $event->getRdv();
        $form = $event->getForm();
        $supportGroup = $event->getSupportGroup();

        if (null !== $supportGroup) {
            $rdv->setSupportGroup($supportGroup);
        }

        if ($form->get('_googleCalendar')->getData()) {
            $rdv->setGoogleEventId(true);
        } elseif ($rdv->getGoogleEventId()) {
            $rdv->setGoogleEventId(null);
        }

        if ($form->get('_outlookCalendar')->getData()) {
            $rdv->setOutlookEventId(true);
        } elseif ($rdv->getOutlookEventId()) {
            $rdv->setOutlookEventId(null);
        }
    }

    /**
     * Supprime les rendez-vous en cache du suivi et de l'utlisateur.
     */
    public function discache(RdvEvent $event): bool
    {
        $rdv = $event->getRdv();

        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        if ($supportGroup = $rdv->getSupportGroup()) {
            $cache->deleteItems([
                SupportGroup::CACHE_SUPPORT_LAST_RDV_KEY.$supportGroup->getId(),
                SupportGroup::CACHE_SUPPORT_NEXT_RDV_KEY.$supportGroup->getId(),
                SupportGroup::CACHE_SUPPORT_RDVS_KEY.$supportGroup->getId(),
            ]);

            if (null === $rdv->getId() || $rdv->getCreatedAt()->format('U') === $rdv->getUpdatedAt()->format('U')) {
                $cache->deleteItem(SupportGroup::CACHE_SUPPORT_NB_RDVS_KEY.$supportGroup->getId());
            }
        }

        return $cache->deleteItem(User::CACHE_USER_RDVS_KEY.$rdv->getCreatedBy()->getId());
    }
}
