<?php

namespace App\Service\Payment;

use App\Entity\Support\Payment;
use App\Entity\Support\SupportGroup;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class PaymentManager
{
    public static function deleteCacheItems(Payment $payment): bool
    {
        $supportGroup = $payment->getSupportGroup();

        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        if ($payment->getDeletedAt() || $payment->getCreatedAt() == $payment->getUpdatedAt()) {
            $cache->deleteItem(SupportGroup::CACHE_SUPPORT_NB_PAYMENTS_KEY.$supportGroup->getId());
        }

        return $cache->deleteItem(SupportGroup::CACHE_SUPPORT_PAYMENTS_KEY.$supportGroup->getId());
    }
}
