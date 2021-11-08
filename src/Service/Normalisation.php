<?php

namespace App\Service;

use PhpOffice\PhpSpreadsheet\Shared\Date;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Normalisation
{
    public $normalizer;
    public $translator;

    public function __construct(NormalizerInterface $normalizer, TranslatorInterface $translator)
    {
        $this->normalizer = $normalizer;
        $this->translator = $translator;
    }

    /**
     * Normalise l'entité.
     */
    public function normalize(object $entity, string $name = null): array
    {
        $array = $this->normalizer->normalize($entity, null, ['groups' => 'export']);

        foreach ($array as $key => $value) {
            if ($value && stristr($key, 'Date')) {
                $array[$key] = Date::stringToExcel(substr($value, 0, 10));
            } elseif (stristr($key, 'Amt')) {
                $array[$key] = (string) $value;
            }
            $newKey = $key.'#'.$name;
            $array[$newKey] = $array[$key];
            unset($array[$key]);
        }

        return $array;
    }

    /**
     * Donne la clé.
     */
    public function getKeys(array $array, array $translationFile = ['forms']): array
    {
        $arrayKeys = [];
        foreach ($array as $value) {
            if (stristr($value, '#')) {
                $array = explode('#', $value, 2);
                $nameEntity = $array[1];
                $value = $this->unCamelCase(str_replace('ToString', '', $array[0]), ' ', $translationFile);
                $nameEntity = $this->unCamelCase($nameEntity, ' ', $translationFile);
                $arrayKeys[] = $value.' ['.$nameEntity.']';
            } else {
                $arrayKeys[] = $value;
            }
        }

        return $arrayKeys;
    }

    /**
     * Inverse l'écriture en camelCase.
     */
    public function unCamelCase(string $content, string $separator = ' ', array $translationFiles = ['forms']): string
    {
        $content = preg_replace('#(?<=[a-zA-Z])([A-Z])(?=[a-zA-Z])#', $separator.'$1', $content);
        $content = ucfirst(strtolower($content));

        foreach ($translationFiles as $file) {
            $content = $this->translator->trans($content, [], $file);
        }

        return $content;
    }
}
