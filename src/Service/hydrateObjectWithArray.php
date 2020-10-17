<?php

namespace App\Service;

trait hydrateObjectWithArray
{
    /**
     * Hydrate un objet à partir d'un tableau.
     */
    public function hydrateObjectWithArray(object $object, array $array): object
    {
        foreach ($array as $key => $value) {
            $method = 'set'.ucfirst($key);
            if (method_exists($object, $method)) {
                $object->$method($value);
            }
        }

        return $object;
    }
}
