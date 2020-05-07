<?php

namespace App\Controller;

use App\Controller\Traits\CacheTrait;
use App\Entity\Document;
use App\Service\Pagination;
use App\Entity\SupportGroup;
use App\Service\FileUploader;
use App\Form\Model\DocumentSearch;
use App\Form\Document\DocumentType;
use App\Repository\DocumentRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\Document\DocumentSearchType;
use App\Repository\SupportGroupRepository;
use App\Controller\Traits\ErrorMessageTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DocumentController extends AbstractController
{
    use ErrorMessageTrait;
    use CacheTrait;

    private $manager;
    private $repo;
    private $repoSupportGroup;

    public function __construct(EntityManagerInterface $manager, DocumentRepository $repo, SupportGroupRepository $repoSupportGroup)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->repoSupportGroup = $repoSupportGroup;
    }

    /**
     * Liste des documents du suivi.
     *
     * @Route("support/{supportId}/documents", name="support_documents", methods="GET|POST")
     */
    public function listDocuments(int $supportId, DocumentSearch $search = null, Request $request, Pagination $pagination): Response
    {
        $supportGroup = $this->repoSupportGroup->findSupportById($supportId);

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
     * @Route("support/{supportId}/document/new", name="document_new", methods="POST")
     */
    public function newDocument($supportId, Request $request, FileUploader $fileUploader): Response
    {
        $supportGroup = $this->repoSupportGroup->findSupportById($supportId);

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

        // $this->discachedSupport($supportGroup);

        return $this->json([
            'code' => 200,
            'action' => 'create',
            'alert' => 'success',
            'msg' => 'Le document "'.$document->getName().'" est enregistré.',
            'data' => [
                'documentId' => $document->getId(),
                'groupPeopleId' => $groupPeople->getId(),
                'type' => $document->getTypeToString(),
                'path' => $path.'/'.$fileName,
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
