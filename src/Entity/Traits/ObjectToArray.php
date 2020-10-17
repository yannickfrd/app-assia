<?php

namespace App\Entity\Traits;

trait ObjectToArray
{
    public function toArray(): array
    {
        $array = [];

        foreach ($this as $key => $value) {
            $array[$key] = $value;
        }

        return $array;
    }
}
