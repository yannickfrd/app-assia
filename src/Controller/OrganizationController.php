<?php

namespace App\Controller;

use App\Entity\Organization;
use App\Form\Organization\OrganizationType;
use App\Repository\OrganizationRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrganizationController extends AbstractController
{
    private $manager;
    private $repo;

    public function __construct(EntityManagerInterface $manager, OrganizationRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
    }

    /**
     * Affiche la liste des dispositifs
     * 
     * @Route("/admin/organizations", name="admin_organizations")
     * @param Request $request
     * @param Pagination $pagination
     * @return Response
     */
    public function listOrganization(Request $request, Pagination $pagination): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        $organizations = $pagination->paginate($this->repo->findAllOrganizationsQuery(), $request);

        return $this->render("app/organization/listOrganizations.html.twig", [
            "organizations" => $organizations ?? null
        ]);
    }

    /**
     * Nouveau dispositif
     * 
     * @Route("/admin/organization/new", name="admin_organization_new", methods="GET|POST")
     * @param Organization $organization
     * @param Request $request
     * @return Response
     */
    public function newOrganization(Organization $organization = null, Request $request): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        $organization = new Organization();

        $form = $this->createForm(OrganizationType::class, $organization);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createOrganization($organization);
        }

        return $this->render("app/organization/organization.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => false
        ]);
    }

    /**
     * Crée un dispositif
     *
     * @param Organization $organization
     */
    protected function createOrganization(Organization $organization)
    {
        $now = new \DateTime();

        $organization->setCreatedAt($now)
            ->setCreatedBy($this->getUser())
            ->setUpdatedAt($now)
            ->setUpdatedBy($this->getUser());

        $this->manager->persist($organization);
        $this->manager->flush();

        $this->addFlash("success", "Le dispositif a été créé.");

        return $this->redirectToRoute("admin_organizations");
    }

    /**
     * Modification d'un dispositif
     * 
     * @Route("/admin/organization/{id}", name="admin_organization_edit", methods="GET|POST")
     * @param Organization $organization
     * @param Request $request
     * @return Response
     */
    public function editOrganization(Organization $organization, Request $request): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");

        $form = $this->createForm(OrganizationType::class, $organization);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->updateOrganization($organization);
        }

        $this->addFlash("success", "Le dispositif a été mis à jour.");

        return $this->render("app/organization/organization.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }

    /**
     * Met à jour un dispositif
     *
     * @param Organization $organization
     */
    protected function updateOrganization(Organization $organization)
    {
        $organization->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->getUser());

        $this->manager->flush();

        $this->addFlash("success", "Les modifications ont été enregistrées.");
        return $this->redirectToRoute("admin_organizations");
    }
}
