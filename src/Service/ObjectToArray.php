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
    protected $translation;
    protected $initEvalGroup = null;
    protected $initEvalPerson = null;
    protected $evalSocialGroup = null;
    protected $evalSocialPerson = null;
    protected $evalAdmPerson = null;
    protected $evalFamilyGroup = null;
    protected $evalFamilyPerson = null;
    protected $evalProfPerson = null;
    protected $evalBudgetGroup = null;
    protected $evalBudgetPerson = null;
    protected $evalHousingGroup = null;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    // Retourne l'objet en array
    public function getArray($object, $nameObject = null, $translation = null)
    {
        $this->nameObject = $nameObject;
        $this->translation = $translation;

        return array_combine(
            $this->{$this->nameObject} ? $this->{$this->nameObject} :  $this->getKeys($object),
            $this->getValues($object)
        );
    }

    // Retourne les clés d'un object
    protected function getKeys(Object $object)
    {
        $keys = [];
        $transNameObject = $this->unCamelCase($this->nameObject);
        foreach ((array) $object as $key => $value) {
            $key = explode("\x00", $key);
            $key = array_pop($key);
            if ($key != "id" && !stristr($key, "evaluation")) {
                $keys[] = $this->unCamelCase($key) . " [" . $transNameObject . "]";
            }
        }
        $this->{$this->nameObject} = $keys;
        return $keys;
    }

    // Inverse l'écriture en camelCase
    protected function unCamelCase($content, $separator = " ")
    {
        $content = preg_replace("#(?<=[a-zA-Z])([A-Z])(?=[a-zA-Z])#", $separator . "$1", $content);
        $content = ucfirst(strtolower($content));

        return $this->translator->trans($content, [], $this->translation);
    }

    // Retourne les valeurs d'un objet
    protected function getValues(Object $object)
    {
        $array = (array) $object;
        $values = [];

        foreach ($array as $key => $value) {
            $key = explode("\x00", $key);
            $key = array_pop($key);
            if ($key != "id" && !stristr($key, "evaluation")) {
                $values[] = $this->getValue($object, $key, $value);
            }
        }
        return $values;
    }

    protected function getValue($object, $key, $value)
    {
        if (is_int($value)) {
            $method = "get" . ucfirst($key) . "List";
            if (method_exists($object, $method)) {
                return  $object->$method($value);
            }
        }
        if (is_object($value)) {
            if (method_exists($value, "getId")) {
                return $value->getId();
            }
            return Date::PHPToExcel($value->format("Y-m-d"));
        }
        if (is_bool($value) && $value) {
            return "Oui";
        }
        return $value;
    }
}
