<?php

namespace App\Controller;

use App\Entity\Document;
use App\Service\Pagination;
use App\Entity\SupportGroup;
use App\Service\FileUploader;
use App\Form\Model\DocumentSearch;
use App\Form\Document\DocumentType;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\Document\DocumentSearchType;
use App\Controller\Traits\ErrorMessageTrait;
use App\Service\Download;
use App\Service\SupportGroup\SupportGroupService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
    public function listDocuments(int $id, SupportGroupService $supportGroupService, Request $request, Pagination $pagination): Response
    {
        $supportGroup = $supportGroupService->getSupportGroup($id);

        $this->denyAccessUnlessGranted('VIEW', $supportGroup);

        $search = new DocumentSearch();

        $formSearch = $this->createForm(DocumentSearchType::class, $search);
        $formSearch->handleRequest($request);

        $form = $this->createForm(DocumentType::class, new Document());

        return $this->render('app/document/listDocuments.html.twig', [
            'support' => $supportGroup,
            'form_search' => $formSearch->createView(),
            'form' => $form->createView(),
            'documents' => $pagination->paginate($this->repo->findAllDocumentsQuery($supportGroup->getId(), $search), $request),
        ]);
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
     *
     * @return mixed
     */
    public function readDocument(Document $document, Download $download)
    {
        $this->denyAccessUnlessGranted('VIEW', $document);

        $file = 'uploads/documents/'.$document->getGroupPeople()->getId().'/'.$document->getCreatedAt()->format('Y/m').'/'.$document->getInternalFileName();

        if (file_exists($file)) {
            return $download->send($file);
        }

        $this->addFlash('danger', 'Ce fichier n\'existe pas.');

        return $this->redirectToRoute('support_documents', ['supportId' => $document->getSupportGroup()->getId()]);
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

        $file = 'uploads/documents/'.$document->getSupportGroup()->getGroupPeople()->getId().'/'.$document->getInternalFileName();

        if (file_exists($file)) {
            unlink($file);
        }

        $this->manager->remove($document);
        $this->manager->flush();

        return $this->json([
            'code' => 200,
            'action' => 'delete',
            'alert' => 'warning',
            'msg' => 'Le document "'.$documentName.'" est supprimé.',
        ], 200);
    }

    /**
     * Crée un document une fois le formulaire soumis et validé.
     */
    protected function createDocument(SupportGroup $supportGroup, $form, FileUploader $fileUploader, Document $document): Response
    {
        $file = $form['file']->getData();

        $groupPeople = $supportGroup->getGroupPeople();

        $path = '/'.$groupPeople->getId().'/'.(new \DateTime())->format('Y/m');

        $fileName = $fileUploader->upload($file, $path);

        $size = filesize($fileUploader->getTargetDirectory().$path.'/'.$fileName);

        $document->setInternalFileName($fileName)
            ->setSize($size)
            ->setGroupPeople($groupPeople)
            ->setSupportGroup($supportGroup);

        $supportGroup->setUpdatedAt(new \DateTime());

        $this->manager->persist($document);
        $this->manager->flush();

        return $this->json([
            'code' => 200,
            'action' => 'create',
            'alert' => 'success',
            'msg' => 'Le document "'.$document->getName().'" est enregistré.',
            'data' => [
                'documentId' => $document->getId(),
                'groupPeopleId' => $groupPeople->getId(),
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
}
