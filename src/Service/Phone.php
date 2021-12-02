<?php

namespace App\Service;

class Phone
{
    public static function getPhoneFormat($phone): string
    {
        return preg_replace('#(..)(..)(..)(..)(..)#', '$1 $2 $3 $4 $5', $phone);
    }

    public static function formatPhone($phone): string
    {
        return preg_replace('#[-./_, ]#', '', $phone);
    }
}
