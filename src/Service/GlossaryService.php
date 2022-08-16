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
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\Event\Rdv;
use App\Entity\Event\Task;
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
use App\Entity\Support\PlacePerson;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class GlossaryService
{
    protected $translator;
    protected $normalizer;

    public const TO_IGNORE = [
        'id',
        'createdAt',
        'createdBy',
        'updatedAt',
        'updatedBy',
        'deletedAt',
        'disabledAt',
    ];

    public const TRANSLATED_TYPES = [
        'int' => 'Nombre',
        'float' => 'Nombre',
        'DateTimeInterface' => 'Date',
        'DateTime' => 'Date',
        'string' => 'Texte',
    ];

    public function __construct(TranslatorInterface $translator, NormalizerInterface $normalizer)
    {
        $this->translator = $translator;
        $this->normalizer = $normalizer;
    }

    public function getDatas(array $entities = null): array
    {
        if (null === $entities) {
            $entities = $this->getEntities();
        }

        $datas = [];

        foreach ($entities as $entitie) {
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
                    /** @var object $type */
                    $type = $reflectionMethod->getReturnType();
                    if ($type && !in_array(lcfirst($propertyName), self::TO_IGNORE)
                        && !str_contains($propertyName, 'ToString')
                        && array_key_exists($type->getName(), self::TRANSLATED_TYPES)) {
                        $typeName = $type->getName();
                        $values = $this->getConstValues($typeName, $shortName, $reflectionClass, $propertyName);

                        $properties[$propertyName] = [
                            'entity' => lcfirst($className),
                            'trans_entity' => $transClassName,
                            'name' => lcfirst($propertyName),
                            'trans_name' => $this->trans($propertyName),
                            'type' => $typeName,
                            'trans_type' => self::TRANSLATED_TYPES[$typeName],
                            'values' => $values ?? null,
                            'values_to_string' => isset($values) && is_array($values) ? join(', ', $values) : null,
                        ];
                    }
                }
            }
            $datas[$reflectionClass->getShortName()] = $properties;
        }

        return $datas;
    }

    protected function getEntities(): iterable
    {
        yield User::class;
        yield Person::class;
        yield RolePerson::class;
        yield PeopleGroup::class;
        yield SupportGroup::class;
        yield SupportPerson::class;
        yield OriginRequest::class;
        yield Avdl::class;
        yield HotelSupport::class;
        yield EvaluationGroup::class;
        yield EvaluationPerson::class;
        yield EvalAdmPerson::class;
        yield EvalProfPerson::class;
        yield EvalBudgetGroup::class;
        yield EvalBudgetPerson::class;
        yield EvalFamilyGroup::class;
        yield EvalFamilyPerson::class;
        yield EvalSocialGroup::class;
        yield EvalSocialPerson::class;
        yield EvalHousingGroup::class;
        yield EvalJusticePerson::class;
        yield Referent::class;
        yield Note::class;
        yield Rdv::class;
        yield Task::class;
        yield Document::class;
        yield Payment::class;
        yield Service::class;
        yield Pole::class;
        yield Place::class;
        yield PlaceGroup::class;
        yield PlacePerson::class;
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
    public function trans(string $content, string $separator = ' ', array $translationFiles = ['app', 'forms', 'evaluation']): string
    {
        $content = preg_replace('#(?<=[a-zA-Z])([A-Z])(?=[a-zA-Z])#', $separator.'$1', $content);
        $content = ucfirst(strtolower($content));

        foreach ($translationFiles as $file) {
            $content = $this->translator->trans($content, [], $file);
        }

        return $content;
    }
}
