<?php

namespace App\Service\Export;

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
use App\Entity\Support\Avdl;
use App\Entity\Support\HotelSupport;
use App\Entity\Support\OriginRequest;
use App\Entity\Support\SupportPerson;
use App\Form\Model\Admin\ExportSearch;
use App\Service\ExportExcel;
use App\Service\Normalisation;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class EvaluationPersonExport extends ExportExcel
{
    use SupportPersonDataTrait;
    use EvaluationPersonDataTrait;

    protected $normalisation;
    protected $logger;
    protected $datas;
    protected $anonymized;

    protected $originRequest;

    protected $evalInitGroup;
    protected $evalInitPerson;

    protected $evaluationPerson;
    protected $evalJusticePerson;
    protected $evalSocialPerson;
    protected $evalAdmPerson;
    protected $evalBudgetPerson;
    protected $evalFamilyPerson;
    protected $evalProfPerson;

    protected $evaluationGroup;
    protected $evalSocialGroup;
    protected $evalFamilyGroup;
    protected $evalBudgetGroup;
    protected $evalHousingGroup;

    protected $avdl;
    protected $hotelSupport;

    public function __construct(Normalisation $normalisation, LoggerInterface $logger)
    {
        $this->normalisation = $normalisation;
        $this->logger = $logger;

        $this->originRequest = new OriginRequest();

        $this->evalInitGroup = new EvalInitGroup();
        $this->evalInitPerson = new EvalInitPerson();

        $this->evaluationGroup = new EvaluationGroup();
        $this->evalBudgetGroup = new EvalBudgetGroup();
        $this->evalFamilyGroup = new EvalFamilyGroup();
        $this->evalHousingGroup = new EvalHousingGroup();
        $this->evalSocialGroup = new EvalSocialGroup();

        $this->evaluationPerson = new EvaluationPerson();
        $this->evalJusticePerson = new EvalJusticePerson();
        $this->evalAdmPerson = new EvalAdmPerson();
        $this->evalBudgetPerson = new EvalBudgetPerson();
        $this->evalFamilyPerson = new EvalFamilyPerson();
        $this->evalProfPerson = new EvalProfPerson();
        $this->evalSocialPerson = new EvalSocialPerson();

        $this->avdl = new Avdl();
        $this->hotelSupport = new HotelSupport();
    }

    /**
     * Exporte les donn??es.
     *
     * @param SupportPerson[] $supports
     *
     * @return Response|string
     */
    public function exportData(array $supports, ExportSearch $search, bool $asynch = true)
    {
        $this->logger->info('Nb results: '.count($supports));
        $this->logger->info('Used memory after database query: '.number_format(memory_get_usage(), 0, ',', ' '));

        $this->anonymized = $search->getAnonymized();
        $getDatas = 'light' === $search->getModel() ? 'getEvaluationPersonDatas' : 'getFullDatas';

        $arrayData = [];

        if (count($supports) > 0) {
            $arrayData[] = $this->normalisation->getKeys(array_keys($this->$getDatas($supports[0])), ['forms', 'evaluation']);
        }

        foreach ($supports as $supportPerson) {
            $arrayData[] = $this->$getDatas($supportPerson);
        }

        $this->logger->info('Used memory after normalize datas: '.number_format(memory_get_usage(), 0, ',', ' '));

        $this->createSheet($arrayData, [
            'name' => 'export_suivis',
            'columnsWidth' => $search->getModel() ? null : 15,
            'formatted' => $search->getFormattedSheet(),
            'modelPath' => $search->getModel() ?
                \dirname(__DIR__).'/../../public/documentation/models/model_export_evaluation_'.$search->getModel().'.xlsx' : null,
            'startCell' => $search->getModel() ? 'A2' : 'A1',
        ]);

        $this->logger->info('Used memory after create sheet: '.number_format(memory_get_usage(), 0, ',', ' '));

        return $this->exportFile($asynch);
    }

    protected function getFullDatas(SupportPerson $supportPerson): array
    {
        $this->datas = $this->getSupportPersonDatas($supportPerson, $this->anonymized);
        $evaluations = $supportPerson->getEvaluations();
        $supportGroup = $supportPerson->getSupportGroup();
        $evaluationPerson = $evaluations[$evaluations->count() - 1] ?? $this->evaluationPerson;
        $evaluationGroup = $evaluationPerson->getEvaluationGroup() ?? $this->evaluationGroup;

        $referentsType = [];
        $referentsName = [];

        foreach ($supportGroup->getPeopleGroup()->getReferents() as $referent) {
            $referentsType[] = $referent->getTypeToString();
            $referentsName[] = $referent->getName();
        }

        $this->datas = array_merge($this->datas, [
            'Service(s) r??f??rent(s) - Type' => join(', ', $referentsType),
            'Service(s) r??f??rent(s) - Nom' => $this->anonymized ? 'XXX' : join(', ', $referentsName),
            'ID ??valuation groupe' => $evaluationGroup->getId(),
            'Compl??tude de l\'??valuation (%)' => (string) $supportGroup->getEvaluationScore(),
        ]);

        $this->add($supportGroup->getEvalInitGroup() ?? $this->evalInitGroup, 'evalInit');
        $this->add($supportPerson->getEvalInitPerson() ?? $this->evalInitPerson, 'evalInit');
        $this->add($evaluationPerson->getEvalJusticePerson() ?? $this->evalJusticePerson, 'justice');
        $this->add($evaluationGroup->getEvalSocialGroup() ?? $this->evalSocialGroup, 'social');
        $this->add($evaluationPerson->getEvalSocialPerson() ?? $this->evalSocialPerson, 'social');
        $this->add($evaluationPerson->getEvalAdmPerson() ?? $this->evalAdmPerson, 'adm');
        $this->add($evaluationGroup->getEvalFamilyGroup() ?? $this->evalFamilyGroup, 'family');
        $this->add($evaluationPerson->getEvalFamilyPerson() ?? $this->evalFamilyPerson, 'family');
        $this->add($evaluationPerson->getEvalProfPerson() ?? $this->evalProfPerson, 'prof');
        $this->add($evaluationGroup->getEvalBudgetGroup() ?? $this->evalBudgetGroup, 'budget');
        $this->add($evaluationPerson->getEvalBudgetPerson() ?? $this->evalBudgetPerson, 'budget');
        $this->add($evaluationGroup->getEvalHousingGroup() ?? $this->evalHousingGroup, 'housing');

        $this->add($supportGroup->getAvdl() ?? $this->avdl, 'avdl');
        $this->add($supportGroup->getHotelSupport() ?? $this->hotelSupport, 'pash');

        return $this->datas;
    }
}
