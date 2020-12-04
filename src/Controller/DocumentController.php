<?php

namespace App\Controller;

use App\Controller\Traits\ErrorMessageTrait;
use App\Entity\Document;
use App\Entity\SupportGroup;
use App\EntityManager\SupportManager;
use App\Form\Document\DocumentSearchType;
use App\Form\Document\DocumentType;
use App\Form\Model\DocumentSearch;
use App\Repository\DocumentRepository;
use App\Service\Download;
use App\Service\FileUploader;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DocumentController extends AbstractController
{
    use ErrorMessageTrait;

    private $manager;
    private $repo;

    public function __construct(EntityManagerInterface $manager, DocumentRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
    }

    /**
     * Liste des documents du suivi.
     *
     * @Route("support/{id}/documents", name="support_documents", methods="GET|POST")
     */
    public function listDocuments(int $id, SupportManager $supportManager, Request $request, Pagination $pagination): Response
    {
        $supportGroup = $supportManager->getSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $search = new DocumentSearch();

        $formSearch = $this->createForm(DocumentSearchType::class, $search);
        $formSearch->handleRequest($request);

        $form = $this->createForm(DocumentType::class, new Document());

        return $this->render('app/document/listDocuments.html.twig', [
            'support' => $supportGroup,
            'form_search' => $formSearch->createView(),
            'form' => $form->createView(),
            'documents' => $this->getDocuments($supportGroup, $request, $search, $pagination),
        ]);
    }

    /**
     * Donne les documents du suivi.
     */
    protected function getDocuments(SupportGroup $supportGroup, Request $request, DocumentSearch $search, Pagination $pagination)
    {
        // Si filtre ou tri utilisé, n'utilise pas le cache.
        if ($request->query->count() > 0) {
            return  $pagination->paginate($this->repo->findAllDocumentsQuery($supportGroup->getId(), $search), $request);
        }

        // Sinon, récupère les documents en cache.
        return (new FilesystemAdapter())->get(SupportGroup::CACHE_SUPPORT_DOCUMENTS_KEY.$supportGroup->getId(),
            function (CacheItemInterface $item) use ($supportGroup, $pagination, $search, $request) {
                $item->expiresAfter(\DateInterval::createFromDateString('7 days'));

                return $pagination->paginate($this->repo->findAllDocumentsQuery($supportGroup->getId(), $search), $request);
            }
        );
    }

    /**
     * Nouveau document.
     *
     * @Route("support/{id}/document/new", name="document_new", methods="POST")
     */
    public function newDocument(SupportGroup $supportGroup, Request $request, FileUploader $fileUploader): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $supportGroup);

        $document = new Document();

        $form = ($this->createForm(DocumentType::class, $document))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createDocument($supportGroup, $form, $fileUploader, $document);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * Lit le document.
     *
     * @Route("document/{id}/read", name="document_read", methods="GET")
     */
    public function readDocument(Document $document, Download $download): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $document);

        $file = 'uploads/documents/'.$document->getPeopleGroup()->getId().'/'.$document->getCreatedAt()->format('Y/m').'/'.$document->getInternalFileName();

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
     */
    public function editDocument(Document $document, Request $request): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $document);

        $form = ($this->createForm(DocumentType::class, $document))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->updateDocument($document);
        }

        return $this->getErrorMessage($form);
    }

    /**
     * Supprime un document.
     *
     * @Route("document/{id}/delete", name="document_delete", methods="GET")
     * @IsGranted("DELETE", subject="document")
     */
    public function deleteDocument(Document $document): Response
    {
        $documentName = $document->getName();

        $file = 'uploads/documents/'.$document->getSupportGroup()->getPeopleGroup()->getId().'/'.$document->getInternalFileName();

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

        return $this->json([
            'code' => 200,
            'action' => 'delete',
            'alert' => 'warning',
            'msg' => "Le document $documentName est supprimé.",
        ], 200);
    }

    /**
     * Crée un document une fois le formulaire soumis et validé.
     */
    protected function createDocument(SupportGroup $supportGroup, $form, FileUploader $fileUploader, Document $document): Response
    {
        $file = $form['file']->getData();

        $peopleGroup = $supportGroup->getPeopleGroup();

        $path = '/'.$peopleGroup->getId().'/'.(new \DateTime())->format('Y/m');

        $fileName = $fileUploader->upload($file, $path);

        $size = \filesize($fileUploader->getTargetDirectory().$path.'/'.$fileName);

        $document->setInternalFileName($fileName)
            ->setSize($size)
            ->setPeopleGroup($peopleGroup)
            ->setSupportGroup($supportGroup);

        $supportGroup->setUpdatedAt(new \DateTime());

        $this->manager->persist($document);
        $this->manager->flush();

        $this->discache($supportGroup);

        return $this->json([
            'code' => 200,
            'action' => 'create',
            'alert' => 'success',
            'msg' => 'Le document "'.$document->getName().'" est enregistré.',
            'data' => [
                'documentId' => $document->getId(),
                'peopleGroupId' => $peopleGroup->getId(),
                'type' => $document->getTypeToString(),
                'size' => $size,
                'createdAt' => $document->getCreatedAt()->format('d/m/Y H:i'),
            ],
        ], 200);
    }

    /**
     * Met à jour le document une fois le formulaire soumis et validé.
     */
    protected function updateDocument(Document $document): Response
    {
        $this->manager->flush();

        $this->discache($document->getSupportGroup(), true);

        return $this->json([
            'code' => 200,
            'action' => 'update',
            'alert' => 'success',
            'msg' => 'Les informations du document "'.$document->getName().'" sont mises à jour.',
            'data' => [
                'type' => $document->getTypeToString(),
            ],
        ], 200);
    }

    /**
     * Supprime les documents en cache du suivi.
     */
    protected function discache(SupportGroup $supportGroup, $isUpdate = false): bool
    {
        $cache = new FilesystemAdapter();

        if (false === $isUpdate) {
            $cache->deleteItem(SupportGroup::CACHE_SUPPORT_NB_DOCUMENTS_KEY.$supportGroup->getId());
        }

        return $cache->deleteItem(SupportGroup::CACHE_SUPPORT_DOCUMENTS_KEY.$supportGroup->getId());
    }
}
