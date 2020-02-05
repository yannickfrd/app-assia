<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Transforme un object en tableau associatif
 */
class ObjectToArray
{
    protected $translator;
    protected $nameObject;
    protected $sitSocial = null;
    protected $sitAdm = null;
    protected $sitFamily = null;
    protected $sitFamilyGroup = null;
    protected $sitFamilyPerson = null;
    protected $sitProf = null;
    protected $sitBudgetGroup = null;
    protected $sitBudget = null;
    protected $sitHousing = null;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    // Retourne l'objet en array
    public function getArray($emtpyObject, $object, $nameObject = null)
    {
        $this->nameObject = $nameObject;

        if ($this->{$this->nameObject}) {
            $objectKeys = $this->{$this->nameObject};
        } else {
            $objectKeys = $this->getKeys($emtpyObject);
        }

        $objectValues = $this->getValues($object ?? $emtpyObject);

        return array_combine($objectKeys, $objectValues);
    }

    // Retourne les clés d'un object
    protected function getKeys(Object $object)
    {
        $keys = [];
        $transNameObject = $this->unCamelCase($this->nameObject);
        foreach ((array) $object as $key => $value) {
            $key = explode("\x00", $key);
            $key = array_pop($key);
            $keys[] = $this->unCamelCase($key) . " (" . $transNameObject . ")";
        }
        $this->{$this->nameObject} = $keys;
        return $keys;
    }

    // Inverse l'écriture en camelCase
    protected function unCamelCase($content, $separator = " ")
    {
        $content = preg_replace("#(?<=[a-zA-Z])([A-Z])(?=[a-zA-Z])#", $separator . "$1", $content);
        $content = ucfirst(strtolower($content));

        return $this->translator->trans($content, [], $this->nameObject);
    }

    // Retourne les valeurs d'un objet
    protected function getValues(Object $object)
    {
        $array = (array) $object;
        $values = [];

        foreach ($array as $key => $value) {
            if (is_int($value)) {
                $key = array_pop(explode("\x00", $key));
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
                    $values[] = Date::PHPToExcel($value->format("Y-m-d"));
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
                        $values[] = "";
                        break;
                }
            } else {
                $values[] = $value;
            }
        }
        return $values;
    }
}
