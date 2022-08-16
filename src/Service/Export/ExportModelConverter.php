<?php

namespace App\Service\Export;

use App\Entity\Admin\ExportModel;
use App\Entity\Evaluation\EvalAdmPerson;
use App\Entity\Evaluation\EvalBudgetGroup;
use App\Entity\Evaluation\EvalBudgetPerson;
use App\Entity\Evaluation\EvalFamilyGroup;
use App\Entity\Evaluation\EvalFamilyPerson;
use App\Entity\Evaluation\EvalHousingGroup;
use App\Entity\Evaluation\EvalInitGroup;
use App\Entity\Evaluation\EvalInitPerson;
use App\Entity\Evaluation\EvalJusticePerson;
use App\Entity\Evaluation\EvalProfPerson;
use App\Entity\Evaluation\EvalSocialGroup;
use App\Entity\Evaluation\EvalSocialPerson;
use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\People\PeopleGroup;
use App\Entity\People\Person;
use App\Entity\Support\Avdl;
use App\Entity\Support\HotelSupport;
use App\Entity\Support\OriginRequest;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use App\Form\Model\Admin\ExportSearch;
use App\Repository\Admin\ExportModelRepository;
use App\Repository\Support\SupportPersonRepository;
use App\Service\ExportExcel;
use App\Service\GlossaryService;
use Doctrine\Common\Annotations\AnnotationReader;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PropertyInfo\Extractor\SerializerExtractor;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;

class ExportModelConverter
{
    public function __construct(
        private SupportPersonRepository $supportPersonRepo,
        private GlossaryService $glossaryService,
        private ExportModelRepository $exportModelRepo,
    ) {
    }

    public function save(ExportModel $exportModel, Request $request): ExportModel
    {
        $modelData = [];
        $i = 0;

        foreach ($request->request->all() as $key => $value) {
            if (in_array($key, $this->getExportModelProperties()) || '_token' === $key) {
                continue;
            }

            $values = explode('_', $key);
            $transValue = str_replace('ToString', '', end($values));

            $editValues = [];
            $editValues[] = $values[0];

            for ($j = 1; $j < count($values); ++$j) {
                $editValues[] = 'get'.\ucfirst($values[$j]);
            }

            $modelData[$i] = [
                'id' => $i + 1,
                'order' => $i + 1,
                'label' => $this->glossaryService->trans($transValue).' - '.
                    $this->glossaryService->trans($values[count($values) - 2]),
                'values' => $editValues,
            ];

            ++$i;
        }

        $exportModel->setContent($modelData);

        $this->exportModelRepo->add($exportModel, true);

        return $exportModel;
    }

    /**
     * @param SupportPerson[] $supports
     */
    public function exportData(array $supports, ExportSearch $search): Response|string
    {
        $exportModel = $this->exportModelRepo->findOneBy(['id' => $search->getModel()]);

        $datas = [];

        if (count($supports) > 0) {
            $datas[] = array_keys($this->convert($exportModel, $supports[0]));
        }

        foreach ($supports as $supportPerson) {
            $datas[] = $this->convert($exportModel, $supportPerson);
        }

        $exportExcel = new ExportExcel();

        $exportExcel->createSheet($datas, [
            'name' => 'export_suivis',
            'columnsWidth' => 15,
            'formatted' => true,
            'modelPath' => null,
            'startCell' => 'A1',
        ]);

        return $exportExcel->exportFile(true);
    }

    public function convert(ExportModel $exportModel, SupportPerson $supportPerson): array
    {
        $supportGroup = $supportPerson->getSupportGroup();
        $evaluationPerson = $supportPerson->getFirstEvaluation();
        $evaluationGroup = $evaluationPerson?->getEvaluationGroup();

        $datas = [];

        foreach ($exportModel->getContent()  as $item) {
            $values = $item['values'];
            $value = ${$values[0]};

            for ($i = 1; $i < count($values); ++$i) {
                $value = $value?->{$values[$i]}();
            }
            if ($value instanceof \DateTime) {
                $value = Date::PHPToExcel($value->format('Y-m-d'));
            }
            $datas[$item['label']] = $value;
        }

        return $datas;
    }

    public function getData(): array
    {
        $datas = [];
        foreach ($this->getEntities() as $key => $entity) {
            if (true === is_iterable($entity)) {
                foreach ($entity as $value) {
                    $datas[] = $this->getReflectionClassProperties($value, $key);
                }
            } else {
                $datas[] = $this->getReflectionClassProperties($entity);
            }
        }

        return $datas;
    }

    private function getReflectionClassProperties(object|string $class, ?string $prefixEntity = null): array
    {
        $serializerClassMetadataFactory = new ClassMetadataFactory(
            new AnnotationLoader(new AnnotationReader())
        );

        $serializerExtractor = new SerializerExtractor($serializerClassMetadataFactory);

        $exportableProperties = $serializerExtractor->getProperties($class, ['serializer_groups' => ['exportable']]);
        $prefixEntity = $prefixEntity ? explode('\\', $prefixEntity) : null;
        $prefixEntity = $prefixEntity ? lcfirst(end($prefixEntity)).'_' : '';
        $explodedClassName = explode('\\', $class);
        $className = end($explodedClassName);
        $transClassName = $this->glossaryService->trans($className);
        $className = lcfirst($className);
        $oroperties = [];

        foreach ($exportableProperties as $property) {
            $oroperties[$property] = [
                'class_name' => $className,
                'trans_class_name' => $transClassName,
                'name' => $prefixEntity.$className.'_'.$property,
                'trans_name' => $this->glossaryService->trans(str_replace('ToString', '', $property)),
            ];
        }

        return $oroperties;
    }

    protected function getEntities(): iterable
    {
        yield SupportPerson::class => [Person::class];
        yield SupportGroup::class => [PeopleGroup::class];

        yield SupportGroup::class;
        yield SupportPerson::class;
        yield SupportGroup::class => [
            OriginRequest::class,
            Avdl::class,
            HotelSupport::class,
        ];

        yield EvaluationGroup::class => [EvalInitGroup::class];
        yield EvaluationPerson::class => [EvalInitPerson::class];
        yield EvaluationPerson::class => [EvalJusticePerson::class];
        yield EvaluationGroup::class => [EvalSocialGroup::class];
        yield EvaluationPerson::class => [
            EvalSocialPerson::class,
            EvalAdmPerson::class,
        ];
        yield EvaluationGroup::class => [EvalFamilyGroup::class];
        yield EvaluationPerson::class => [
            EvalFamilyPerson::class,
            EvalProfPerson::class,
        ];
        yield EvaluationGroup::class => [EvalBudgetGroup::class];
        yield EvaluationPerson::class => [EvalBudgetPerson::class];
        yield EvaluationGroup::class => [EvalHousingGroup::class];
    }

    private function getExportModelProperties(): array
    {
        $reflectClass = new \ReflectionClass(ExportModel::class);

        $properties = [];
        foreach ($reflectClass->getProperties() as $property) {
            $properties[] = $property->getName();
        }

        return $properties;
    }
}
