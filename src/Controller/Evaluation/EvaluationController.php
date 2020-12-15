<?php

namespace App\Controller\Evaluation;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Evaluation\EvaluationPerson;
use App\Entity\Evaluation\InitEvalGroup;
use App\Entity\Evaluation\InitEvalPerson;
use App\Entity\Support\SupportGroup;
use App\Entity\Support\SupportPerson;
use App\EntityManager\SupportManager;
use App\Form\Evaluation\EvaluationGroupType;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Repository\Organization\ReferentRepository;
use App\Repository\Support\RdvRepository;
use App\Repository\Support\SupportGroupRepository;
use App\Service\ExportPDF;
use App\Service\ExportWord;
use App\Service\Normalisation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Environment;

class EvaluationController extends AbstractController
{
    use ErrorMessageTrait;

    private $manager;
    private $repoSupportGroup;
    private $repoEvaluation;

    public function __construct(EntityManagerInterface $manager, SupportGroupRepository $repoSupportGroup, EvaluationGroupRepository $repoEvaluation)
    {
        $this->manager = $manager;
        $this->repoSupportGroup = $repoSupportGroup;
        $this->repoEvaluation = $repoEvaluation;
    }

    /**
     * Voir une évaluation sociale.
     *
     * @Route("/support/{id}/evaluation/show", name="support_evaluation_show", methods="GET|POST")
     */
    public function showEvaluation(int $id, Request $request): Response
    {
        $evaluationGroup = $this->repoEvaluation->findEvaluationOfSupport($id);

        if (null === $evaluationGroup) {
            return $this->createEvaluationGroup($this->repoSupportGroup->findSupportById($id));
        }

        $supportGroup = $evaluationGroup->getSupportGroup();

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $form = ($this->createForm(EvaluationGroupType::class, $evaluationGroup))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->updateEvaluation($evaluationGroup);
        }

        return $this->render('app/evaluation/evaluationEdit.html.twig', [
            'support' => $supportGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification d'une évaluation sociale.
     *
     * @Route("/support/{id}/evaluation/edit", name="support_evaluation_edit", methods="POST")
     */
    public function editEvaluation(int $id, Request $request, Normalisation $normalisation): Response
    {
        $evaluationGroup = $this->repoEvaluation->findEvaluationOfSupport($id);

        $supportGroup = $evaluationGroup->getSupportGroup();

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $form = ($this->createForm(EvaluationGroupType::class, $evaluationGroup))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->updateAjax($evaluationGroup);
        }

        return $this->getErrorMessage($form, $normalisation);
    }

    /**
     * Générer une note à partir de la dernière évaluation sociale du suivi.
     *
     * @Route("support/{id}/evaluation/export/{type}", name="evaluation_export", methods="GET")
     */
    public function exportEvaluation(int $id, string $type, Request $request, SupportManager $supportManager, ReferentRepository $repoReferent, EvaluationGroupRepository $repoEvaluation, RdvRepository $repoRdv, Environment $renderer): Response
    {
        $export = $type === 'word' ? new ExportWord(true) : new ExportPDF();

        $supportGroup = $supportManager->getFullSupportGroup($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $title = 'Grille d\'évaluation sociale';
        $logoPath = $supportGroup->getService()->getPole()->getLogoPath();
        $fullnameSupport = $supportManager->getHeadPersonSupport($supportGroup)->getFullname();

        $content = $renderer->render('app/evaluation/evaluationExport.html.twig', [
            'type' => $type,
            'support' => $supportGroup,
            'referents' => $supportManager->getReferents($supportGroup->getPeopleGroup(), $repoReferent),
            'evaluation' => $supportManager->getEvaluation($supportGroup, $repoEvaluation),
            'lastRdv' => $supportManager->getLastRdvs($supportGroup, $repoRdv),
            'nextRdv' => $supportManager->getNextRdvs($supportGroup, $repoRdv),
            'title' => $title,
            'logo_path' => $type === 'pdf' ? $export->getPathImage($logoPath) : null,
            'header_info' => 'ESPERER 95 | '.$title.' | '.$fullnameSupport,
        ]);

        $export->createDocument($content, $title, $logoPath, $fullnameSupport);

        return $export->download($request->server->get('HTTP_USER_AGENT') != 'Symfony BrowserKit');
    }

    /**
     * Crée l'évaluation sociale du groupe.
     */
    protected function createEvaluationGroup(SupportGroup $supportGroup)
    {
        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $evaluationGroup = (new EvaluationGroup())
            ->setSupportGroup($supportGroup)
            ->setDate(new \DateTime());

        $initEvalGroup = (new InitEvalGroup())->setSupportGroup($supportGroup);

        $this->manager->persist($initEvalGroup);

        $supportGroup->setInitEvalGroup($initEvalGroup);
        $evaluationGroup->setInitEvalGroup($supportGroup->getInitEvalGroup());

        $this->manager->persist($evaluationGroup);

        foreach ($supportGroup->getSupportPeople() as $supportPerson) {
            $this->createEvaluationPerson($supportPerson, $evaluationGroup);
        }

        $this->manager->flush();

        return $this->redirectToRoute('support_evaluation_show', ['id' => $supportGroup->getId()]);
    }

    /**
     * Crée l'évaluation sociale d'une personne du groupe.
     */
    protected function createEvaluationPerson(SupportPerson $supportPerson, EvaluationGroup $evaluationGroup)
    {
        $evaluationPerson = (new EvaluationPerson())
            ->setEvaluationGroup($evaluationGroup)
            ->setSupportPerson($supportPerson);

        $initEvalPerson = (new InitEvalPerson())
            ->setSupportPerson($supportPerson);

        $this->manager->persist($initEvalPerson);

        $supportPerson->setInitEvalPerson($initEvalPerson);
        $evaluationPerson->setInitEvalPerson($supportPerson->getInitEvalPerson());

        $this->manager->persist($evaluationPerson);
    }

    /**
     * Met à jour l'évaluation sociale du groupe.
     */
    protected function updateEvaluation(EvaluationGroup $evaluationGroup)
    {
        $now = new \DateTime();

        $evaluationGroup->setUpdatedAt($now);

        $evaluationGroup->getSupportGroup()
            ->setUpdatedAt($now)
            ->setUpdatedBy($this->getUser());

        $this->updateBudgetGroup($evaluationGroup);

        $this->manager->persist($evaluationGroup);
        $this->manager->flush();

        $this->discache($evaluationGroup);

        $this->addFlash('success', 'Les modifications sont enregistrées.');
    }

    /**
     * Met à jour l'évaluation sociale du groupe.
     */
    protected function updateAjax(EvaluationGroup $evaluationGroup)
    {
        $now = new \DateTime();

        $evaluationGroup->setUpdatedAt($now);

        $supportGroup = $evaluationGroup->getSupportGroup();
        $supportGroup->setUpdatedAt($now)
            ->setUpdatedBy($this->getUser());

        $this->updateBudgetGroup($evaluationGroup);

        $this->manager->persist($evaluationGroup);
        $this->manager->flush();

        $this->discache($evaluationGroup);

        return $this->json([
            'code' => 200,
            'alert' => 'success',
            'msg' => 'Les modifications sont enregistrées.',
            'date' => $evaluationGroup->getUpdatedAt()->format('d/m/Y à H:i'),
            'user' => $this->getUser()->getFullName(),
        ], 200);
    }

    /**
     * Met à jour le budget du groupe.
     */
    protected function updateBudgetGroup(EvaluationGroup $evaluationGroup)
    {
        $resourcesGroupAmt = 0;
        $chargesGroupAmt = 0;
        $debtsGroupAmt = 0;
        $monthlyRepaymentAmt = 0;
        // Ressources et dettes initiales
        $initResourcesGroupAmt = 0;
        $initDebtsGroupAmt = 0;

        foreach ($evaluationGroup->getEvaluationPeople() as $evaluationPerson) {
            $evalBudgetPerson = $evaluationPerson->getEvalBudgetPerson();
            if ($evalBudgetPerson) {
                $resourcesGroupAmt += $evalBudgetPerson->getResourcesAmt();
                $chargesGroupAmt += $evalBudgetPerson->getChargesAmt();
                $debtsGroupAmt += $evalBudgetPerson->getDebtsAmt();
                $monthlyRepaymentAmt += $evalBudgetPerson->getMonthlyRepaymentAmt();
            }

            $initEvalPerson = $evaluationPerson->getInitEvalPerson();
            if ($initEvalPerson) {
                $initResourcesGroupAmt += $initEvalPerson->getResourcesAmt();
                $initDebtsGroupAmt += $initEvalPerson->getDebtsAmt();
            }
        }

        $evalBudgetGroup = $evaluationGroup->getEvalBudgetGroup();
        $evalBudgetGroup->setResourcesGroupAmt($resourcesGroupAmt);
        $evalBudgetGroup->setChargesGroupAmt($chargesGroupAmt);
        $evalBudgetGroup->setDebtsGroupAmt($debtsGroupAmt);
        $evalBudgetGroup->setMonthlyRepaymentAmt($monthlyRepaymentAmt);
        $evalBudgetGroup->setBudgetBalanceAmt($resourcesGroupAmt - $chargesGroupAmt - $evalBudgetGroup->getContributionAmt() - $monthlyRepaymentAmt);
        // Ressources et dettes initiales
        $evaluationGroup->getInitEvalGroup()->setResourcesGroupAmt($initResourcesGroupAmt);
        $evaluationGroup->getInitEvalGroup()->setDebtsGroupAmt($initDebtsGroupAmt);
    }

    protected function discache(EvaluationGroup $evaluationGroup)
    {
        return (new FilesystemAdapter())->deleteItem(EvaluationGroup::CACHE_EVALUATION_KEY.$evaluationGroup->getSupportGroup()->getId());
    }
}
