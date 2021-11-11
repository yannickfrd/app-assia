<?php

namespace App\Controller\Evaluation;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Evaluation\EvaluationGroup;
use App\Event\Evaluation\EvaluationEvent;
use App\Form\Evaluation\EvaluationGroupType;
use App\Repository\Evaluation\EvaluationGroupRepository;
use App\Repository\Support\SupportGroupRepository;
use App\Service\Evaluation\EvaluationCreator;
use App\Service\Evaluation\EvaluationExporter;
use App\Service\Normalisation;
use App\Service\SupportGroup\SupportManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EvaluationController extends AbstractController
{
    use ErrorMessageTrait;

    private $manager;
    private $supportGroupRepo;
    private $evaluationRepo;

    public function __construct(
        EntityManagerInterface $manager,
        SupportGroupRepository $supportGroupRepo,
        EvaluationGroupRepository $evaluationRepo
    ) {
        $this->manager = $manager;
        $this->supportGroupRepo = $supportGroupRepo;
        $this->evaluationRepo = $evaluationRepo;
    }

    /**
     * Créer une évaluation sociale.
     *
     * @Route("/support/{id}/evaluation/new", name="support_evaluation_new", methods="GET")
     */
    public function createEvaluation(int $id, EvaluationCreator $evaluationCreator): Response
    {
        if ($this->evaluationRepo->count(['supportGroup' => $id])) {
            return $this->redirectToRoute('support_evaluation_view', ['id' => $id]);
        }

        $this->denyAccessUnlessGranted('EDIT', $supportGroup = $this->supportGroupRepo->find($id));

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
        if (0 === $this->evaluationRepo->count(['supportGroup' => $id])) {
            return $this->redirectToRoute('support_evaluation_new', ['id' => $id]);
        }

        $evaluationGroup = $this->evaluationRepo->findEvaluationOfSupport($id);
        $supportGroup = $evaluationGroup->getSupportGroup();

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $form = $this->createForm(EvaluationGroupType::class, $evaluationGroup)
            ->handleRequest($request);

        return $this->render('app/evaluation/edit/evaluationEdit.html.twig', [
            'support' => $supportGroup,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modifier une évaluation sociale.
     *
     * @Route("/support/{id}/evaluation/edit", name="support_evaluation_edit", methods="POST")
     */
    public function editEvaluation(int $id, Request $request, EventDispatcherInterface $dispatcher, Normalisation $normalisation): Response
    {
        if (null === $evaluationGroup = $this->evaluationRepo->findEvaluationOfSupport($id)) {
            throw $this->createAccessDeniedException();
        }

        $this->denyAccessUnlessGranted('EDIT', $evaluationGroup->getSupportGroup());

        $form = $this->createForm(EvaluationGroupType::class, $evaluationGroup)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dispatcher->dispatch(new EvaluationEvent($evaluationGroup), 'evaluation.before_update');

            $this->manager->persist($evaluationGroup);
            $this->manager->flush();

            $dispatcher->dispatch(new EvaluationEvent($evaluationGroup), 'evaluation.after_update');

            return $this->json([
                'alert' => 'success',
                'msg' => 'Les modifications sont enregistrées.',
                'data' => [
                    'updatedAt' => $evaluationGroup->getUpdatedAt()->format('d/m/Y à H:i'),
                    'updatedBy' => $this->getUser()->getFullName(),
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
    public function deleteEvaluationGroup(EvaluationGroup $evaluationGroup): Response
    {
        $supportGroup = $evaluationGroup->getSupportGroup();

        $this->denyAccessUnlessGranted('DELETE', $supportGroup);

        $evaluationGroup->getInitEvalGroup()->setSupportGroup(null);
        foreach ($evaluationGroup->getEvaluationPeople() as $evaluationPerson) {
            $evaluationPerson->getInitEvalPerson()->setSupportPerson(null);
        }

        $this->manager->remove($evaluationGroup);
        $this->manager->flush();

        $this->addFlash('warning', "L'évaluation sociale est supprimée.");

        (new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']))->deleteItem(
            EvaluationGroup::CACHE_EVALUATION_KEY.$supportGroup->getId()
        );

        return $this->redirectToRoute('support_view', ['id' => $supportGroup->getId()]);
    }

    /**
     * Exporter une évaluation sociale au format Word ou PDF.
     *
     * @Route("/support/{id}/evaluation/export/{type}", name="evaluation_export", methods="GET")
     */
    public function exportEvaluation(int $id, SupportManager $supportManager, EvaluationExporter $evaluationExporter, Request $request): Response
    {
        $supportGroup = $supportManager->getFullSupportGroup($id);

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $response = $evaluationExporter->export($supportGroup, $request);

        if (!$response) {
            $this->addFlash('warning', 'Il n\'y a pas d\'évaluation sociale créée pour ce suivi.');

            return $this->redirectToRoute('support_view', ['id' => $id]);
        }

        return $response;
    }
}
