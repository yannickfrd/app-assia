<?php

namespace App\Controller\Support;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Support\Document;
use App\Entity\Support\SupportGroup;
use App\EntityManager\SupportManager;
use App\Form\Model\Support\DocumentSearch;
use App\Form\Model\Support\SupportDocumentSearch;
use App\Form\Support\Document\ActionType;
use App\Form\Support\Document\DocumentSearchType;
use App\Form\Support\Document\DocumentType;
use App\Form\Support\Document\DropzoneDocumentType;
use App\Form\Support\Document\SupportDocumentSearchType;
use App\Repository\Support\DocumentRepository;
use App\Security\CurrentUserService;
use App\Service\File\Download;
use App\Service\File\FileDownloader;
use App\Service\File\FileUploader;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DocumentController extends AbstractController
{
    use ErrorMessageTrait;

    private $manager;
    private $repo;
    private $documentsDirectory;

    public function __construct(EntityManagerInterface $manager, DocumentRepository $repo, string $documentsDirectory)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->documentsDirectory = $documentsDirectory;
    }

    /**
     * Liste des documents.
     *
     * @Route("/documents", name="documents", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function viewListDocuments(Request $request, Pagination $pagination, CurrentUserService $currentUser): Response
    {
        $form = ($this->createForm(DocumentSearchType::class, $search = new DocumentSearch()))
            ->handleRequest($request);

        return $this->render('app/support/document/listDocuments.html.twig', [
            'form' => $form->createView(),
            'documents' => $pagination->paginate($this->repo->findDocumentsQuery($search, $currentUser), $request, 20),
        ]);
    }

    /**
     * Liste des documents du suivi.
     *
     * @Route("support/{id}/documents", name="support_documents", methods="GET|POST")
     */
    public function listSupportDocuments(int $id, SupportManager $supportManager, Request $request, Pagination $pagination): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $supportGroup = $supportManager->getSupportGroup($id));

        $formSearch = ($this->createForm(SupportDocumentSearchType::class, $search = new SupportDocumentSearch()))
            ->handleRequest($request);

        $documentForm = $this->createForm(DocumentType::class, new Document());
        $dropzoneForm = $this->createForm(DropzoneDocumentType::class, null, [
            'action' => $this->generateUrl('document_new', ['id' => $supportGroup->getId()]),
        ]);
        $actionForm = $this->createForm(ActionType::class, null, [
            'action' => $this->generateUrl('document_download', ['id' => $supportGroup->getId()]),
        ]);

        return $this->render('app/support/document/supportDocuments.html.twig', [
            'support' => $supportGroup,
            'form_search' => $formSearch->createView(),
            'documentForm' => $documentForm->createView(),
            'dropzoneForm' => $dropzoneForm->createView(),
            'actionForm' => $actionForm->createView(),
            'documents' => $pagination->paginate($this->repo->findSupportDocumentsQuery($supportGroup, $search), $request),
        ]);
    }

    /**
     * Nouveau document.
     *
     * @Route("support/{id}/document/new", name="document_new", methods="POST")
     * @IsGranted("EDIT", subject="supportGroup")
     */
    public function newDocument(SupportGroup $supportGroup, Request $request, FileUploader $fileUploader): Response
    {
        $files = $request->files->get('files');

        if (!$request->files->get('dropzone_document')['files'] && $files) {
            $request->files->set('dropzone_document', ['files' => [$files]]);
        }

        $form = ($this->createForm(DropzoneDocumentType::class))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $fileUploader->createDocuments($supportGroup, $request->files->all());

            $this->manager->flush();

            $this->discache($supportGroup);

            return $this->json($data);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * Lit le document.
     *
     * @Route("document/{id}/read", name="document_read", methods="GET")
     * @IsGranted("VIEW", subject="document")
     */
    public function readDocument(Document $document, Download $download): Response
    {
        $file = $this->getFilePath($document);

        if (file_exists($file)) {
            return $download->send($file);
        }

        $this->addFlash('danger', 'Ce fichier n\'existe pas.');

        return $this->redirectToRoute('support_documents', ['id' => $document->getSupportGroup()->getId()]);
    }

    /**
     * Modification d'un document.
     *
     * @Route("document/{id}/edit", name="document_edit", methods="POST")
     * @IsGranted("EDIT", subject="document")
     */
    public function editDocument(Document $document, Request $request, NormalizerInterface $normalizer): Response
    {
        $form = ($this->createForm(DocumentType::class, $document))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();

            $this->discache($document->getSupportGroup(), true);

            return $this->json([
                'action' => 'update',
                'alert' => 'success',
                'msg' => 'Les informations du document "'.$document->getName().'" sont mises à jour.',
                'data' => $normalizer->normalize($document, null, ['groups' => ['get', 'view']]),
            ]);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * Modification d'un document.
     *
     * @Route("support/{id}/documents/download", name="document_download", methods="GET|POST")
     */
    public function downloadDocuments(int $id, Request $request, SupportManager $supportManager, FileDownloader $downloader): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $supportGroup = $supportManager->getSupportGroup($id));

        $form = ($this->createForm(ActionType::class, null))
            ->handleRequest($request);

        $items = json_decode($request->request->get('items'));

        if ($form->isSubmitted() && $form->isValid() && count($items) > 0) {
            return $this->json($downloader->send($items, $supportGroup));
        }

        return $this->getErrorMessage($form);
    }

    /**
     * Supprime un document.
     *
     * @Route("document/{id}/delete", name="document_delete", methods="GET")
     * @IsGranted("DELETE", subject="document", message="Vous n'avez pas les droits pour effectuer cette action.")
     */
    public function deleteDocument(Document $document): Response
    {
        $file = $this->getFilePath($document);

        $data = [
            'action' => 'delete',
            'alert' => 'warning',
            'msg' => "Le document \"{$document->getName()}\" est supprimé.",
            'data' => [
                'id' => $document->getId(),
                'name' => $document->getName(),
            ],
        ];

        $this->manager->remove($document);
        $this->manager->flush();

        $count = $this->repo->count([
            'peopleGroup' => $document->getSupportGroup()->getPeopleGroup(),
            'internalFileName' => $document->getInternalFileName(),
        ]);

        if (1 === $count && \file_exists($file)) {
            \unlink($file);
        }

        $this->discache($document->getSupportGroup());

        return $this->json($data);
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
    //         return  $pagination->paginate($this->repo->findSupportDocumentsQuery($supportGroup, $search), $request);
    //     }

    //     // Sinon, récupère les documents en cache.
    //     return (new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']))->get(SupportGroup::CACHE_SUPPORT_DOCUMENTS_KEY.$supportGroup->getId(),
    //         function (CacheItemInterface $item) use ($supportGroup, $pagination, $search, $request) {
    //             $item->expiresAfter(\DateInterval::createFromDateString('7 days'));

    //             return $pagination->paginate($this->repo->findSupportDocumentsQuery($supportGroup, $search), $request);
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
