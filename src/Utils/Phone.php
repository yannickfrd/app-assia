<?php

namespace App\Utils;

class Phone
{
    public static function getPhoneFormat($phone)
    {
        $phone = preg_replace("#(..)(..)(..)(..)(..)#", "$1 $2 $3 $4 $5", $phone);
        return $phone;
    }


    public static function formatPhone($phone)
    {
        $phone = preg_replace("#[-./_ ]#", "", $phone);
        return $phone;
    }
}
