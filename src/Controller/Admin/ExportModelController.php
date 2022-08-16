<?php

namespace App\Controller\Admin;

use App\Entity\Admin\ExportModel;
use App\Form\Admin\ExportModelType;
use App\Repository\Admin\ExportModelRepository;
use App\Service\Export\ExportModelConverter;
use App\Service\Pagination;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
#[IsGranted('ROLE_SUPER_ADMIN')]
class ExportModelController extends AbstractController
{
    public function __construct(
        private ExportModelRepository $exportModelRepo,
    ) {
    }

    #[Route('/export-models', name: 'export_model_index', methods: ['GET'])]
    public function index(Pagination $paginator, Request $request): Response
    {
        return $this->render('admin/export_model/export_model_index.html.twig', [
            'export_models' => $paginator->paginate($this->exportModelRepo->findAllExportModelsQuery(), $request),
        ]);
    }

    #[Route('/export-model/new', name: 'export_model_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ExportModelConverter $exportModelConverter): Response
    {
        $exportModel = new ExportModel();

        $form = $this->createForm(ExportModelType::class, $exportModel, [
            'entities' => $entities = $exportModelConverter->getData(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $exportModelConverter->save($exportModel, $request);

            $this->addFlash('success', 'export_model.created_successfully');

            return $this->redirectToRoute('export_model_edit', ['id' => $exportModel->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/export_model/export_model_new.html.twig', [
            'form' => $form,
            'entities' => $entities,
        ]);
    }

    #[Route('/export-model/{id}/edit', name: 'export_model_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ExportModel $exportModel): Response
    {
        $form = $this->createForm(ExportModelType::class, $exportModel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->exportModelRepo->add($exportModel, true);

            $this->addFlash('success', 'export_model.updated_successfully');

            return $this->redirectToRoute('export_model_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('admin/export_model/export_model_edit.html.twig', [
            'export_model' => $exportModel,
            'form' => $form,
        ]);
    }

    #[Route('/export-model/{id}/delete', name: 'export_model_delete', methods: ['POST'])]
    public function delete(Request $request, ExportModel $exportModel): Response
    {
        if ($this->isCsrfTokenValid('delete'.$exportModel->getId(), $request->request->get('_token'))) {
            $this->exportModelRepo->remove($exportModel, true);

            $this->addFlash('warning', 'export_model.deleted_successfully');
        }

        return $this->redirectToRoute('export_model_index', [], Response::HTTP_SEE_OTHER);
    }
}
