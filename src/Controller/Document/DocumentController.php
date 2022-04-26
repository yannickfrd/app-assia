<?php

declare(strict_types=1);

namespace App\Controller\Document;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Support\Document;
use App\Entity\Support\SupportGroup;
use App\Form\Model\Support\DocumentSearch;
use App\Form\Model\Support\SupportDocumentSearch;
use App\Form\Support\Document\ActionType;
use App\Form\Support\Document\DocumentSearchType;
use App\Form\Support\Document\DocumentType;
use App\Form\Support\Document\DropzoneDocumentType;
use App\Form\Support\Document\SupportDocumentSearchType;
use App\Repository\Support\DocumentRepository;
use App\Service\Document\DocumentManager;
use App\Service\File\Downloader;
use App\Service\File\FileDownloader;
use App\Service\File\FileUploader;
use App\Service\Pagination;
use App\Service\SupportGroup\SupportManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DocumentController extends AbstractController
{
    use ErrorMessageTrait;

    /**
     * @Route("/admin/documents", name="document_index", methods="GET|POST")
     */
    public function index(Request $request, Pagination $pagination, DocumentRepository $documentRepo): Response
    {
        $form = $this->createForm(DocumentSearchType::class, $search = new DocumentSearch())
            ->handleRequest($request);

        return $this->render('app/document/document_index.html.twig', [
            'form' => $form->createView(),
            'documents' => $pagination->paginate($documentRepo->findDocumentsQuery($search, $this->getUser()), $request, 20),
        ]);
    }

    /**
     * @Route("/support/{id}/documents", name="support_document_index", methods="GET|POST")
     */
    public function indexSupportDocuments(
        int $id,
        DocumentRepository $documentRepo,
        SupportManager $supportManager,
        Request $request,
        Pagination $pagination
    ): Response {
        $this->denyAccessUnlessGranted('VIEW', $supportGroup = $supportManager->getSupportGroup($id));

        $formSearch = $this->createForm(SupportDocumentSearchType::class, $search = new SupportDocumentSearch(), [
            'service' => $supportGroup->getService(),
        ]);
        $formSearch->handleRequest($request);

        $documentForm = $this->createForm(DocumentType::class, (new Document())->setSupportGroup($supportGroup));

        $dropzoneForm = $this->createForm(DropzoneDocumentType::class, null, [
            'action' => $this->generateUrl('document_create', ['id' => $supportGroup->getId()]),
        ]);
        $actionForm = $this->createForm(ActionType::class, null, [
            'action' => $this->generateUrl('documents_download', ['id' => $supportGroup->getId()]),
        ]);

        return $this->render('app/document/support_document_index.html.twig', [
            'support' => $supportGroup,
            'form_search' => $formSearch->createView(),
            'documentForm' => $documentForm->createView(),
            'dropzoneForm' => $dropzoneForm->createView(),
            'action_form' => $actionForm->createView(),
            'documents' => $pagination->paginate(
                $documentRepo->findSupportDocumentsQuery($supportGroup, $search),
                $request
            ),
        ]);
    }

    /**
     * @Route("/support/{id}/document/create", name="document_create", methods="POST")
     * @IsGranted("EDIT", subject="supportGroup")
     */
    public function create(SupportGroup $supportGroup, Request $request, FileUploader $fileUploader): JsonResponse
    {
        $dropzoneDocument = $request->files->get('dropzone_document');
        $files = $request->files->get('files');
        if ($dropzoneDocument && !$dropzoneDocument['files'] && $files) {
            $request->files->set('dropzone_document', ['files' => [$files]]);
        }

        $form = $this->createForm(DropzoneDocumentType::class)
            ->handleRequest($request);

        $files = $form->get('files')->getData();

        if ($form->isSubmitted() && $form->isValid() && count($files) > 0) {
            $data = $fileUploader->createDocuments($supportGroup, $files);

            DocumentManager::deleteCacheItems($supportGroup);

            return $this->json($data, 200, [], ['groups' => Document::SERIALIZER_GROUPS]);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * Télécharge un fichier.
     *
     * @Route("/document/{id}/download", name="document_download", methods="GET")
     * @IsGranted("VIEW", subject="document")
     */
    public function download(Document $document, Downloader $downloader): Response
    {
        $file = $this->getFilePath($document);

        if (file_exists($file)) {
            return $downloader->send($file);
        }

        $this->addFlash('danger', 'Ce fichier n\'existe pas.');

        return $this->redirectToRoute('support_document_index', ['id' => $document->getSupportGroup()->getId()]);
    }

    /**
     * Modification d'un document.
     *
     * @Route("/support/{id}/documents/download", name="documents_download", methods="GET|POST")
     */
    public function downloadDocuments(
        int $id,
        Request $request,
        SupportManager $supportManager,
        FileDownloader $downloader
    ): JsonResponse {
        $this->denyAccessUnlessGranted('VIEW', $supportGroup = $supportManager->getSupportGroup($id));

        $form = $this->createForm(ActionType::class, null)
            ->handleRequest($request);

        $items = $request->request->has('items') ? json_decode($request->request->get('items')) : null;

        if ($form->isSubmitted() && $form->isValid() && $items && count($items) > 0) {
            return $this->json($downloader->sendDocuments($items, $supportGroup));
        }

        return $this->getErrorMessage($form);
    }

    /**
     * @Route("/document/{id}/edit", name="document_edit", methods="POST")
     * @IsGranted("EDIT", subject="document")
     */
    public function edit(
        Document $document,
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator
    ): JsonResponse {
        $form = $this->createForm(DocumentType::class, $document)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            DocumentManager::deleteCacheItems($document->getSupportGroup(), true);

            return $this->json([
                'action' => 'update',
                'alert' => 'success',
                'msg' => $translator->trans('document.updated_successfully', ['%document_title%' => $document->getName()], 'app'),
                'document' => $document,
            ], 200, [], ['groups' => Document::SERIALIZER_GROUPS]);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * @Route("/document/{id}/delete", name="document_delete", methods="GET")
     */
    public function delete(Document $document, EntityManagerInterface $em, TranslatorInterface $translator): JsonResponse
    {
        $em->remove($document);
        $em->flush();

        DocumentManager::deleteCacheItems($document->getSupportGroup());

        return $this->json([
            'action' => 'delete',
            'alert' => 'warning',
            'msg' => $translator->trans('document.deleted_successfully', ['%document_title%' => $document->getName()], 'app'),
            'document' => ['id' => $document->getId()],
        ]);
    }

    /**
     * @Route("/document/{id}/restore", name="document_restore", methods="GET")
     */
    public function restore(
        int $id,
        DocumentRepository $documentRepo,
        EntityManagerInterface $em,
        TranslatorInterface $translator
    ): JsonResponse {
        $document = $documentRepo->findDocument($id, true);
        $supportGroup = $document->getSupportGroup();

        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $document->setDeletedAt(null);
        $em->flush();

        DocumentManager::deleteCacheItems($supportGroup);

        return $this->json([
            'action' => 'restore',
            'alert' => 'success',
            'msg' => $translator->trans('document.restored_successfully', ['%document_title%' => $document->getName()], 'app'),
            'document' => ['id' => $document->getId()],
        ]);
    }

    /**
     * Return the full path of a document.
     */
    private function getFilePath(Document $document): string
    {
        return $this->getParameter('documents_directory')
            .$document->getCreatedAt()->format('Y/m/d/')
            .$document->getPeopleGroup()->getId().'/'
            .$document->getInternalFileName();
    }
}
