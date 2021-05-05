<?php

namespace App\EventDispatcher\Contribution;

use App\Entity\Support\SupportGroup;
use App\Event\Contribution\ContributionEvent;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContributionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'contribution.after_create' => 'discache',
            'contribution.after_update' => 'discache',
        ];
    }

    /**
     * Supprime les rendez-vous en cache du suivi et de l'utlisateur.
     */
    public function discache(ContributionEvent $event): bool
    {
        $contribution = $event->getContribution();
        $supportGroup = $contribution->getSupportGroup();

        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        if (null === $contribution->getId() || $contribution->getCreatedAt()->format('U') === $contribution->getUpdatedAt()->format('U')) {
            $cache->deleteItem(SupportGroup::CACHE_SUPPORT_NB_CONTRIBUTIONS_KEY.$supportGroup->getId());
        }

        return $cache->deleteItem(SupportGroup::CACHE_SUPPORT_CONTRIBUTIONS_KEY.$supportGroup->getId());
    }
}
