<?php

namespace App\Event\Payment;

use App\Entity\Support\Payment;
use App\Entity\Support\SupportGroup;
use Symfony\Contracts\EventDispatcher\Event;

class PaymentEvent extends Event
{
    public const NAME = 'payment.event';

    private $payment;
    private $supportGroup;

    public function __construct(Payment $payment, SupportGroup $supportGroup = null)
    {
        $this->contribution = $payment;
        $this->supportGroup = $supportGroup;
    }

    public function getPayment(): Payment
    {
        return $this->contribution;
    }

    public function getSupportGroup(): SupportGroup
    {
        return $this->supportGroup ?? $this->contribution->getSupportGroup();
    }
}
