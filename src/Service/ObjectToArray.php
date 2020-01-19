<?php

namespace App\Service;

/**
 * Transforme un object en tableau associatif
 */
class ObjectToArray
{
    public function __construct()
    {
    }

    // Retourne les clés d'un object
    public function getKeys(Object $object)
    {
        $array = (array) $object;
        $keys = [];

        $i = 0;
        foreach ($array as $key => $value) {
            if ($i == 0) {
                $key = explode("\x00", $key);
                $keys[] = array_pop($key);
            }
        }
        return $keys;
    }

    // Retourne les valeurs d'un objet
    public function getValues(Object $object)
    {
        $array = (array) $object;
        $values = [];

        foreach ($array as $key => $value) {
            if (is_int($value)) {
                $key = explode("\x00", $key);
                $key = array_pop($key);
                $method = "get" . ucfirst($key) . "List";
                if (method_exists($object, $method)) {
                    $values[] = $object->$method($value);
                } else {
                    $values[] = $value;
                }
            } elseif (is_object($value)) {
                if (method_exists($value, "getId")) {
                    $values[] = $value->getId();
                } else {
                    $values[] = $value->format("d/m/Y");
                }
            } elseif (is_bool($value)) {
                switch ($value) {
                    case true:
                        $values[] = "Oui";
                        break;
                    case false:
                        $values[] = "Non";
                        break;
                    default:
                        break;
                }
            } else {
                $values[] = $value;
            }
        }

        return $values;
    }
}
