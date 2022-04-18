<?php

declare(strict_types=1);

namespace App\Controller\Evaluation;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Evaluation\EvaluationGroup;
use App\Entity\Organization\User;
use App\Entity\Support\SupportGroup;
use App\Form\Evaluation\EvaluationGroupType;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Repository\Support\SupportGroupRepository;
use App\Service\Evaluation\EvaluationCreator;
use App\Service\Evaluation\EvaluationExporter;
use App\Service\Evaluation\EvaluationManager;
use App\Service\Normalisation;
use App\Service\SupportGroup\SupportManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class EvaluationController extends AbstractController
{
    use ErrorMessageTrait;

    private $evaluationRepo;

    public function __construct(EvaluationGroupRepository $evaluationRepo)
    {
        $this->evaluationRepo = $evaluationRepo;
    }

    /**
     * Créer une évaluation sociale.
     *
     * @Route("/support/{id}/evaluation/new", name="support_evaluation_new", methods="GET")
     */
    public function createEvaluation(int $id, SupportGroupRepository $supportGroupRepo,
        EvaluationCreator $evaluationCreator): Response
    {
        if ($this->evaluationRepo->count(['supportGroup' => $id])) {
            return $this->redirectToRoute('support_evaluation_view', ['id' => $id]);
        }

        $this->denyAccessUnlessGranted('EDIT', $supportGroup = $supportGroupRepo->find($id));

        $evaluationCreator->create($supportGroup);

        return $this->redirectToRoute('support_evaluation_view', ['id' => $supportGroup]);
    }

    /**
     * Voir une évaluation sociale.
     *
     * @Route("/support/{id}/evaluation/view", name="support_evaluation_view", methods="GET|POST")
     */
    public function showEvaluation(int $id, Request $request): Response
    {
        $evaluationGroup = $this->evaluationRepo->findEvaluationOfSupport($id);

        if (!$evaluationGroup) {
            return $this->redirectToRoute('support_evaluation_new', ['id' => $id]);
        }

        $supportGroup = $evaluationGroup->getSupportGroup();

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        if ($evaluationGroup->getEvaluationPeople()->count() < $supportGroup->getSupportPeople()->count()) {
            return $this->redirectToRoute('evaluation_fix_people', ['id' => $evaluationGroup->getId()]);
        }

        $form = $this->createForm(EvaluationGroupType::class, $evaluationGroup)
            ->handleRequest($request);

        return $this->renderForm('app/evaluation/edit/evaluation_edit.html.twig', [
            'support' => $supportGroup,
            'form' => $form,
        ]);
    }

    /**
     * Modifier une évaluation sociale.
     *
     * @Route("/support/{id}/evaluation/edit", name="support_evaluation_edit", methods="POST")
     */
    public function editEvaluation(int $id, Request $request, EvaluationManager $evaluationManager,
        Normalisation $normalisation): JsonResponse
    {
        if (null === $evaluationGroup = $this->evaluationRepo->findEvaluationOfSupport($id)) {
            throw $this->createAccessDeniedException();
        }

        $supportGroup = $evaluationGroup->getSupportGroup();

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $form = $this->createForm(EvaluationGroupType::class, $evaluationGroup)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $evaluationManager->updateAndFlush($evaluationGroup);

            /** @var User $user */
            $user = $this->getUser();

            return $this->json([
                'alert' => 'success',
                'msg' => "Les modifications sont enregistrées ({$supportGroup->getEvaluationScore()}% de complétude).",
                'data' => [
                    'updatedAt' => $evaluationGroup->getUpdatedAt()->format('d/m/Y à H:i'),
                    'updatedBy' => $user->getFullname(),
                ],
            ]);
        }

        return $this->getErrorMessage($form, $normalisation, ['evaluation']);
    }

    /**
     * Supprime l'évaluation sociale du suivi.
     *
     * @Route("/evaluation/{id}/delete", name="evaluation_delete", methods="GET")
     */
    public function deleteEvaluationGroup(EvaluationGroup $evaluationGroup, EntityManagerInterface $em): Response
    {
        $supportGroup = $evaluationGroup->getSupportGroup();

        $this->denyAccessUnlessGranted('DELETE', $supportGroup);

        if ($evalInitGroup = $evaluationGroup->getEvalInitGroup()) {
            $evalInitGroup->setSupportGroup(null);
        }

        foreach ($evaluationGroup->getEvaluationPeople() as $evaluationPerson) {
            if ($evalInitPerson = $evaluationPerson->getEvalInitPerson()) {
                $evalInitPerson->setSupportPerson(null);
            }
        }

        $supportGroup->setEvaluationScore(null);

        $em->remove($evaluationGroup);
        $em->flush();

        $this->addFlash('warning', "L'évaluation sociale est supprimée.");

        (new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']))->deleteItems([
            SupportGroup::CACHE_FULLSUPPORT_KEY.$supportGroup->getId(),
            EvaluationGroup::CACHE_EVALUATION_KEY.$supportGroup->getId(),
        ]);

        return $this->redirectToRoute('support_show', ['id' => $supportGroup->getId()]);
    }

    /**
     * Ajoute les personnes du suivi absentes de l'évaluation sociale.
     *
     * @Route("/evaluation/{id}/fix-people", name="evaluation_fix_people", methods="GET")
     */
    public function fixPeople(EvaluationGroup $evaluationGroup, EvaluationManager $evaluationManager): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $evaluationGroup->getSupportGroup());

        $evaluationManager->addEvaluationPeople($evaluationGroup);

        return $this->redirectToRoute('support_evaluation_view', ['id' => $evaluationGroup->getSupportGroup()->getId()]);
    }

    /**
     * Exporter une évaluation sociale au format Word ou PDF.
     *
     * @Route("/support/{id}/evaluation/export/{type}", name="evaluation_export", methods="GET")
     */
    public function exportEvaluation(
        int $id,
        SupportManager $supportManager,
        EvaluationExporter $evaluationExporter,
        Request $request
    ): Response {
        $supportGroup = $supportManager->getFullSupportGroup($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $response = $evaluationExporter->export($supportGroup, $request);

        if (!$response) {
            $this->addFlash('warning', 'Il n\'y a pas d\'évaluation sociale créée pour ce suivi.');

            return $this->redirectToRoute('support_show', ['id' => $id]);
        }

        return $response;
    }
}
