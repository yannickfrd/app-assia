<?php

declare(strict_types=1);

namespace App\Service\Event;

use App\Entity\Event\Rdv;
use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Form\FormInterface;

class RdvManager
{
    public static function addonBeforeFlush(Rdv $rdv, ?FormInterface $form = null, ?SupportGroup $supportGroup = null)
    {
        if (null !== $supportGroup) {
            $rdv->setSupportGroup($supportGroup);
        }

        if ($form->get('_googleCalendar')->getData()) {
            $rdv->setGoogleEventId('1');
        } elseif ($rdv->getGoogleEventId()) {
            $rdv->setGoogleEventId(null);
        }

        if ($form->get('_outlookCalendar')->getData()) {
            $rdv->setOutlookEventId('1');
        } elseif ($rdv->getOutlookEventId()) {
            $rdv->setOutlookEventId(null);
        }
    }

    public static function deleteCacheItems(Rdv $rdv)
    {
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
