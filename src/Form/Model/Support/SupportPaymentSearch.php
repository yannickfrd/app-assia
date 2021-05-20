<?php

namespace App\Form\Model\Support;

use App\Form\Model\Support\Traits\PaymentSearchTrait;
use App\Form\Model\Traits\DateSearchTrait;

class SupportPaymentSearch
{
    use PaymentSearchTrait;
    use DateSearchTrait;

    /** @var int|null */
    private $paymentId;

    public function getPaymentId(): ?int
    {
        return $this->paymentId;
    }

    public function setPaymentId(int $paymentId): self
    {
        $this->paymentId = $paymentId;

        return $this;
    }
}
