<?php

namespace App\Service;

use App\Entity\Evaluation\EvalAdmPerson;
use App\Entity\Evaluation\EvalBudgetGroup;
use App\Entity\Evaluation\EvalBudgetPerson;
use App\Entity\Evaluation\EvalFamilyGroup;
use App\Entity\Evaluation\EvalFamilyPerson;
use App\Entity\Evaluation\EvalHousingGroup;
use App\Entity\Evaluation\EvalJusticePerson;
use App\Entity\Evaluation\EvalProfPerson;
use App\Entity\Evaluation\EvalSocialGroup;
use App\Entity\Evaluation\EvalSocialPerson;
use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Organization\Place;
use App\Entity\Organization\Pole;
use App\Entity\Organization\Referent;
use App\Entity\Organization\Service;
use App\Entity\Organization\User;
use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\People\RolePerson;
use App\Entity\Support\Avdl;
use App\Entity\Support\Document;
use App\Entity\Support\HotelSupport;
use App\Entity\Support\Note;
use App\Entity\Support\OriginRequest;
use App\Entity\Support\Payment;
use App\Entity\Support\PlaceGroup;
use App\Entity\Support\Rdv;
use App\Entity\Support\SupportGroup;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class GlossaryService
{
    protected $translator;
    protected $normalizer;

    protected const TO_IGNORE = [
        'id',
        'createdAt',
        'createdBy',
        'updatedAt',
        'updatedBy',
        'deletedAt',
        'disabledAt',
    ];

    protected const TRANSLATED_TYPES = [
        'int' => 'Nombre',
        'float' => 'Nombre',
        'DateTimeInterface' => 'Date',
        'DateTime' => 'Date',
        'string' => 'Texte',
    ];

    /** @var array */
    protected $data = [];

    public function __construct(TranslatorInterface $translator, NormalizerInterface $normalizer)
    {
        $this->translator = $translator;
        $this->normalizer = $normalizer;
    }

    public function getAll(): array
    {
        foreach ($this->getEntities() as $entitie) {
            $reflectionClass = new \ReflectionClass($entitie);
            $className = $reflectionClass->getShortName();
            $transClassName = $this->trans($className);
            $properties = [];
            foreach ($reflectionClass->getMethods() as $key => $method) {
                /** @var \ReflectionMethod */
                $reflectionMethod = $method;
                $shortName = $reflectionMethod->getShortName();
                if (str_contains($shortName, 'get')) {
                    $propertyName = str_replace('get', '', $shortName);
                    $type = $reflectionMethod->getReturnType();
                    if ($type && !in_array(lcfirst($propertyName), self::TO_IGNORE)
                        && !str_contains($propertyName, 'ToString')
                        && array_key_exists($type->getName(), self::TRANSLATED_TYPES)) {
                        $typeName = $type->getName();
                        $values = $this->getConstValues($typeName, $shortName, $reflectionClass, $propertyName);

                        $properties[$propertyName] = [
                            'name' => $propertyName,
                            'trans_name' => $this->trans($propertyName),
                            'entity' => $className,
                            'trans_entity' => $transClassName,
                            'type' => $typeName,
                            'trans_type' => self::TRANSLATED_TYPES[$typeName],
                            'values' => $values ?? null,
                            'values_to_string' => isset($values) && is_array($values) ? join(', ', $values) : null,
                        ];
                    }
                }
            }
            $this->data[$reflectionClass->getShortName()] = $properties;
        }

        return $this->data;
    }

    protected function getEntities(): array
    {
        return [
            new Person(),
            new RolePerson(),
            new PeopleGroup(),
            new SupportGroup(),
            new Avdl(),
            new HotelSupport(),
            new OriginRequest(),
            new EvaluationGroup(),
            new EvalAdmPerson(),
            new EvalProfPerson(),
            new EvalBudgetGroup(),
            new EvalBudgetPerson(),
            new EvalFamilyGroup(),
            new EvalFamilyPerson(),
            new EvalSocialGroup(),
            new EvalSocialPerson(),
            new EvalHousingGroup(),
            new EvalJusticePerson(),
            new Referent(),
            new Note(),
            new Rdv(),
            new Payment(),
            new Document(),
            new User(),
            new Service(),
            new Pole(),
            new Place(),
            new PlaceGroup(),
        ];
    }

    public function methodExists(string $value, array $methods): bool
    {
        foreach ($methods as $method) {
            if ($value === $method->getShortName()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return mixed
     */
    protected function getConstValues(string $typeName, string $shortName, \ReflectionClass $reflectionClass, string $propertyName)
    {
        if ('int' != $typeName || false === $this->methodExists($shortName.'ToString', $reflectionClass->getMethods())) {
            return null;
        }

        $constName = preg_replace('#(?<=[a-zA-Z])([A-Z])(?=[a-zA-Z])#', '_'.'$1', $propertyName);

        $values = $reflectionClass->getConstant(strtoupper($constName.'s'));

        if ($values) {
            return $values;
        }

        return $reflectionClass->getConstant(strtoupper($constName));
    }

    /**
     * Inverse l'écriture en camelCase.
     */
    public function trans(string $content, string $separator = ' ', array $translationFiles = ['forms', 'evaluation']): string
    {
        $content = preg_replace('#(?<=[a-zA-Z])([A-Z])(?=[a-zA-Z])#', $separator.'$1', $content);
        $content = ucfirst(strtolower($content));

        foreach ($translationFiles as $file) {
            $content = $this->translator->trans($content, [], $file);
        }

        return $content;
    }
}
