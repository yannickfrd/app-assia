<?php

namespace App\Entity\Traits;

trait DurationSupportTrait
{
    /** @Groups({"export", "exportable"}) */
    public function getDuration(?\DateTime $endDate = null): ?int
    {
        if (null === $this->startDate) {
            return null;
        }

        $endDate = $endDate ?? $this->endDate ?? new \DateTime();

        return $endDate->diff($this->startDate)->days;
    }
}
