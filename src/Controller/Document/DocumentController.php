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
use App\Security\CurrentUserService;
use App\Service\File\Downloader;
use App\Service\File\FileDownloader;
use App\Service\File\FileUploader;
use App\Service\Pagination;
use App\Service\SupportGroup\SupportManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class DocumentController extends AbstractController
{
    use ErrorMessageTrait;

    private $em;
    private $documentRepo;
    private $documentsDirectory;

    public function __construct(EntityManagerInterface $em, DocumentRepository $documentRepo, string $documentsDirectory)
    {
        $this->em = $em;
        $this->documentRepo = $documentRepo;
        $this->documentsDirectory = $documentsDirectory;
    }

    /**
     * Liste des documents.
     *
     * @Route("/admin/documents", name="documents", methods="GET|POST")
     */
    public function showListDocuments(Request $request, Pagination $pagination, CurrentUserService $currentUser): Response
    {
        $form = $this->createForm(DocumentSearchType::class, $search = new DocumentSearch())
            ->handleRequest($request);

        return $this->render('app/document/listDocuments.html.twig', [
            'form' => $form->createView(),
            'documents' => $pagination->paginate($this->documentRepo->findDocumentsQuery($search, $currentUser), $request, 20),
        ]);
    }

    /**
     * Liste des documents du suivi.
     *
     * @Route("/support/{id}/documents", name="support_documents", methods="GET|POST")
     */
    public function listSupportDocuments(
        int $id,
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
            'action' => $this->generateUrl('document_new', ['id' => $supportGroup->getId()]),
        ]);
        $actionForm = $this->createForm(ActionType::class, null, [
            'action' => $this->generateUrl('documents_download', ['id' => $supportGroup->getId()]),
        ]);

        return $this->render('app/document/supportDocuments.html.twig', [
            'support' => $supportGroup,
            'form_search' => $formSearch->createView(),
            'documentForm' => $documentForm->createView(),
            'dropzoneForm' => $dropzoneForm->createView(),
            'actionForm' => $actionForm->createView(),
            'documents' => $pagination->paginate(
                $this->documentRepo->findSupportDocumentsQuery($supportGroup, $search),
                $request
            ),
        ]);
    }

    /**
     * Nouveau document.
     *
     * @Route("/support/{id}/document/new", name="document_new", methods="POST")
     * @IsGranted("EDIT", subject="supportGroup")
     */
    public function newDocument(SupportGroup $supportGroup, Request $request, FileUploader $fileUploader): JsonResponse
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

            $this->discache($supportGroup);

            return $this->json($data);
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

        return $this->redirectToRoute('support_documents', ['id' => $document->getSupportGroup()->getId()]);
    }

    /**
     * Modification d'un document.
     *
     * @Route("/support/{id}/documents/download", name="documents_download", methods="GET|POST")
     */
    public function downloadDocuments(int $id, Request $request, SupportManager $supportManager, FileDownloader $downloader): JsonResponse
    {
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
     * Modification d'un document.
     *
     * @Route("/document/{id}/edit", name="document_edit", methods="POST")
     * @IsGranted("EDIT", subject="document")
     */
    public function editDocument(Document $document, Request $request, NormalizerInterface $normalizer): JsonResponse
    {
        $form = $this->createForm(DocumentType::class, $document)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->discache($document->getSupportGroup(), true);

            return $this->json([
                'action' => 'update',
                'alert' => 'success',
                'msg' => 'Les informations du document "'.$document->getName().'" sont mises à jour.',
                'data' => $normalizer->normalize($document, null, ['groups' => ['show_document', 'show_tag', 'view']]),
            ]);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * Supprime un document.
     *
     * @Route("/document/{id}/delete", name="document_delete", methods="GET")
     */
    public function deleteDocument(Document $document): JsonResponse
    {
        $this->em->remove($document);
        $this->em->flush();

        $this->discache($document->getSupportGroup());

        return $this->json([
            'action' => 'delete',
            'alert' => 'warning',
            'msg' => "Le document \"{$document->getName()}\" est supprimé.",
            'data' => [
                'id' => $document->getId(),
                'name' => $document->getName(),
            ],
        ]);
    }

    /**
     * Return the full path of a document.
     */
    private function getFilePath(Document $document): string
    {
        return $this->documentsDirectory.$document->getCreatedAt()->format('Y/m/d/').$document->getPeopleGroup()->getId().'/'.$document->getInternalFileName();
    }

    // /**
    //  * Donne les documents du suivi.
    //  */
    // protected function getDocuments(SupportGroup $supportGroup, Request $request, SupportDocumentSearch $search, Pagination $pagination)
    // {
    //     // Si filtre ou tri utilisé, n'utilise pas le cache.
    //     if ($request->query->count() > 0) {
    //         return  $pagination->paginate($this->documentRepo->findSupportDocumentsQuery($supportGroup, $search), $request);
    //     }

    //     // Sinon, récupère les documents en cache.
    //     return (new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']))->get(SupportGroup::CACHE_SUPPORT_DOCUMENTS_KEY.$supportGroup->getId(),
    //         function (CacheItemInterface $item) use ($supportGroup, $pagination, $search, $request) {
    //             $item->expiresAfter(\DateInterval::createFromDateString('7 days'));

    //             return $pagination->paginate($this->documentRepo->findSupportDocumentsQuery($supportGroup, $search), $request);
    //         }
    //     );
    // }

    /**
     * Supprime les documents en cache du suivi.
     */
    protected function discache(SupportGroup $supportGroup, $isUpdate = false): bool
    {
        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        if (false === $isUpdate) {
            $cache->deleteItem(SupportGroup::CACHE_SUPPORT_NB_DOCUMENTS_KEY.$supportGroup->getId());
        }

        return $cache->deleteItem(SupportGroup::CACHE_SUPPORT_DOCUMENTS_KEY.$supportGroup->getId());
    }
}
