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
use Symfony\Contracts\Translation\TranslatorInterface;

final class EvaluationController extends AbstractController
{
    use ErrorMessageTrait;

    private $evaluationRepo;

    public function __construct(EvaluationGroupRepository $evaluationRepo)
    {
        $this->evaluationRepo = $evaluationRepo;
    }

    /**
     * @Route("/support/{id}/evaluation/new", name="support_evaluation_new", methods="GET")
     */
    public function new(int $id, SupportGroupRepository $supportGroupRepo,
        EvaluationCreator $evaluationCreator): Response
    {
        if ($this->evaluationRepo->count(['supportGroup' => $id])) {
            return $this->redirectToRoute('support_evaluation_show', ['id' => $id]);
        }

        $this->denyAccessUnlessGranted('EDIT', $supportGroup = $supportGroupRepo->find($id));

        $evaluationCreator->create($supportGroup);

        return $this->redirectToRoute('support_evaluation_show', ['id' => $supportGroup]);
    }

    /**
     * @Route("/support/{id}/evaluation/show", name="support_evaluation_show", methods="GET|POST")
     */
    public function show(int $id, Request $request): Response
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
     * @Route("/support/{id}/evaluation/edit", name="support_evaluation_edit", methods="POST")
     */
    public function edit(
        int $id,
        Request $request,
        EvaluationManager $evaluationManager,
        Normalisation $normalisation,
        TranslatorInterface $translator
    ): JsonResponse {
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
                'msg' => $translator->trans('evaluation.updated_successfully',
                    ['rate' => $supportGroup->getEvaluationScore()], 'app'
                ),
                'data' => [
                    'updatedAt' => $evaluationGroup->getUpdatedAt()->format('d/m/Y à H:i'),
                    'updatedBy' => $user->getFullname(),
                ],
            ]);
        }

        return $this->getErrorMessage($form, $normalisation, ['evaluation']);
    }

    /**
     * @Route("/evaluation/{id}/delete", name="evaluation_delete", methods="GET")
     */
    public function delete(EvaluationGroup $evaluationGroup, EntityManagerInterface $em): Response
    {
        $supportGroup = $evaluationGroup->getSupportGroup();

        $this->denyAccessUnlessGranted('DELETE', $supportGroup);

        $supportGroup->setEvaluationScore(null);

        $em->remove($evaluationGroup);
        $em->flush();

        $this->addFlash('warning', 'evaluation.deleted_successfully');

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

        return $this->redirectToRoute('support_evaluation_show', ['id' => $evaluationGroup->getSupportGroup()->getId()]);
    }

    /**
     * Exporter une évaluation sociale au format Word ou PDF.
     *
     * @Route("/support/{id}/evaluation/export/{type}", name="evaluation_export", methods="GET")
     */
    public function export(
        int $id,
        SupportManager $supportManager,
        EvaluationExporter $evaluationExporter,
        Request $request
    ): Response {
        $supportGroup = $supportManager->getFullSupportGroup($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $response = $evaluationExporter->export($supportGroup, $request);

        if (!$response) {
            $this->addFlash('warning', 'evaluation.no_created');

            return $this->redirectToRoute('support_show', ['id' => $id]);
        }

        return $response;
    }
}
