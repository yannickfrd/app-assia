<?php

namespace App\EventDispatcher\Payment;

use App\Entity\Support\SupportGroup;
use App\Event\Payment\PaymentEvent;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'payment.after_create' => 'discache',
            'payment.after_update' => 'discache',
        ];
    }

    /**
     * Supprime les rendez-vous en cache du suivi et de l'utlisateur.
     */
    public function discache(PaymentEvent $event): bool
    {
        $payment = $event->getPayment();
        $supportGroup = $payment->getSupportGroup();

        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);
        if (null === $payment->getId() || $payment->getCreatedAt()->format('U') === $payment->getUpdatedAt()->format('U')) {
            $cache->deleteItem(SupportGroup::CACHE_SUPPORT_NB_PAYMENTS_KEY.$supportGroup->getId());
        }

        return $cache->deleteItem(SupportGroup::CACHE_SUPPORT_PAYMENTS_KEY.$supportGroup->getId());
    }
}
