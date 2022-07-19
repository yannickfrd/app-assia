<?php

namespace App\Service;

class Phone
{
    public static function getPhoneFormat(?string $phone): ?string
    {
        return $phone ? preg_replace('#(..)(..)(..)(..)(..)#', '$1 $2 $3 $4 $5', $phone) : null;
    }

    public static function formatPhone(?string $phone): ?string
    {
        return $phone ? preg_replace('#[-./_, ]#', '', $phone) : null;
    }
}
