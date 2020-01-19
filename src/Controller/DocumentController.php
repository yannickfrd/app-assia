<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\SupportGroup;
use App\Entity\DocumentSearch;

use App\Repository\DocumentRepository;
use App\Repository\SupportGroupRepository;
use Doctrine\ORM\EntityManagerInterface;

use App\Form\Support\Document\DocumentType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\Support\Document\DocumentSearchType;
use App\Service\FileUploader;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DocumentController extends AbstractController
{
    private $manager;
    private $repo;
    private $currentUser;

    public function __construct(EntityManagerInterface $manager, DocumentRepository $repo, Security $security)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->security = $security;
        $this->currentUser = $security->getUser();
    }

    /**
     * Liste des documents du suivi
     * 
     * @Route("support/{id}/documents", name="documents")
     * @param SupportGroupRepository $supportRepo
     * @param DocumentSearch $documentSearch
     * @param Document $document
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function listDocuments($id, SupportGroupRepository $supportRepo, DocumentSearch $documentSearch = null, Request $request, PaginatorInterface $paginator): Response
    {
        $supportGroup = $supportRepo->findSupportById($id);

        $this->denyAccessUnlessGranted("EDIT", $supportGroup);

        $documentSearch = new DocumentSearch;

        $formSearch = $this->createForm(DocumentSearchType::class, $documentSearch);
        $formSearch->handleRequest($request);

        $documents =  $this->paginate($paginator, $supportGroup, $documentSearch, $request);

        $document = new Document();

        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        return $this->render("document/listDocuments.html.twig", [
            "support" => $supportGroup,
            "form_search" => $formSearch->createView(),
            "form" => $form->createView(),
            "documents" => $documents ?? null,
        ]);
    }

    /**
     * Création d'un nouveau document
     * 
     * @Route("support/{id}/document/new", name="document_new", methods="POST")
     * @param SupportGroupRepository $supportRepo
     * @param Request $request
     * @param FileUploader $fileUploader
     * @return Response
     */
    public function newDocument($id, SupportGroupRepository $supportRepo, Request $request, FileUploader $fileUploader): Response
    {
        $supportGroup = $supportRepo->findSupportById($id);

        $this->denyAccessUnlessGranted("EDIT", $supportGroup);

        $document = new Document();

        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createNewDocument($supportGroup, $form, $fileUploader, $document);
        }
        return $this->errorMessage();
    }

    /**
     * Edition d'un document existant
     * 
     * @Route("document/{id}/edit", name="document_edit", methods="POST")
     * @param Document $document
     * @param Request $request
     * @return Response
     */
    public function editDocument(Document $document, Request $request): Response
    {
        $supportGroup = $document->getSupportGroup();

        $this->denyAccessUnlessGranted("EDIT", $supportGroup);

        $form = $this->createForm(DocumentType::class, $document);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->updateDocument($document);
        }
        return $this->errorMessage();
    }

    /**
     * Supprime le document
     * 
     * @Route("document/{id}/delete", name="document_delete", methods="GET")
     * @param Document $document
     * @return Response
     */
    public function deleteDocument(Document $document): Response
    {
        $supportGroup = $document->getSupportGroup();

        $this->denyAccessUnlessGranted("EDIT", $supportGroup);

        $documentName = $document->getName();

        $file = "uploads/documents/" . $supportGroup->getGroupPeople()->getId() . "/" . $document->getInternalFileName();

        if (file_exists($file)) {
            unlink($file);
        }

        $this->manager->remove($document);
        $this->manager->flush();

        return $this->json([
            "code" => 200,
            "action" => "delete",
            "alert" => "warning",
            "msg" => "Le document \"" . $documentName . "\" été supprimé.",
        ], 200);
    }

    /**
     * Pagination de la liste des documents
     *
     * @param PaginatorInterface $paginator
     * @param SupportGroup $supportGroup
     * @param DocumentSearch $documentSearch
     * @param Request $request
     */
    protected function paginate(PaginatorInterface $paginator, SupportGroup $supportGroup, DocumentSearch $documentSearch, Request $request)
    {
        $documents =  $paginator->paginate(
            $this->repo->findAllDocumentsQuery($supportGroup->getId(), $documentSearch),
            $request->query->getInt("page", 1), // page number
            20 // limit per page
        );
        $documents->setCustomParameters([
            "align" => "right", // align pagination
        ]);

        return $documents;
    }

    /**
     * Crée le document une fois le formulaire soumis et validé
     *
     * @param SupportGroup $supportGroup
     * @param FileUploader $fileUploader
     * @param Document $document
     */
    protected function createNewDocument(SupportGroup $supportGroup, $form, FileUploader $fileUploader, Document $document)
    {
        /** @var UploadedFile $file */
        $file = $form["file"]->getData();

        $groupPeople = $supportGroup->getGroupPeople();

        $createdAt = new \DateTime();

        $path = "/" . $groupPeople->getId() . "/" . $createdAt->format("Y/m");

        $fileName = $fileUploader->upload($file, $path);

        $document->setInternalFileName($fileName)
            ->setGroupPeople($groupPeople)
            ->setSupportGroup($supportGroup)
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($createdAt)
            ->setCreatedBy($this->currentUser);

        $this->manager->persist($document);
        $this->manager->flush();

        return $this->json([
            "code" => 200,
            "action" => "create",
            "alert" => "success",
            "msg" => "Le document \"" . $document->getName() . "\" a été enregistré.",
            "data" => [
                "documentId" => $document->getId(),
                "groupPeopleId" => $groupPeople->getId(),
                "typeList" => $document->getTypeList(),
                "path" => $path . "/" . $fileName,
                "createdAt" => date_format($createdAt, "d/m/Y H:i")
            ]
        ], 200);
    }

    /**
     * Met à jour le document une fois le formulaire soumis et validé
     *
     * @param Document $document
     */
    protected function updateDocument(Document $document)
    {
        $document->setUpdatedAt(new \DateTime());
        $this->manager->flush();

        return $this->json([
            "code" => 200,
            "action" => "update",
            "alert" => "success",
            "msg" => "Les informations du document \"" . $document->getName() . "\" ont été mises à jour.",
            "data" => [
                "typeList" => $document->getTypeList(),
            ]
        ], 200);
    }

    // Retourne un message d'erreur au format JSON
    protected function errorMessage()
    {
        return $this->json([
            "code" => 200,
            "alert" => "danger",
            "msg" => "Une erreur s'est produite. Le document n'a pas pu être enregistré.",
        ], 200);
    }
}
