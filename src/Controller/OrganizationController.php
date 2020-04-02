<?php

namespace App\Controller;

use App\Service\Pagination;
use App\Entity\Organization;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\OrganizationRepository;
use App\Form\Organization\OrganizationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
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
     * @Route("/admin/organizations", name="admin_organizations", methods="GET|POST")
     * @IsGranted("ROLE_ADMIN")
     * @param Request $request
     * @param Pagination $pagination
     * @return Response
     */
    public function listOrganization(Request $request, Pagination $pagination): Response
    {
        return $this->render("app/organization/listOrganizations.html.twig", [
            "organizations" => $pagination->paginate($this->repo->findAllOrganizationsQuery(), $request) ?? null
        ]);
    }

    /**
     * Nouveau dispositif
     * 
     * @Route("/admin/organization/new", name="admin_organization_new", methods="GET|POST")
     * @IsGranted("ROLE_ADMIN")
     * @param Organization $organization
     * @param Request $request
     * @return Response
     */
    public function newOrganization(Organization $organization = null, Request $request): Response
    {
        $organization = new Organization();

        $form = ($this->createForm(OrganizationType::class, $organization))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->createOrganization($organization);
        }

        return $this->render("app/organization/organization.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => false
        ]);
    }

    /**
     * Modification d'un dispositif
     * 
     * @Route("/admin/organization/{id}", name="admin_organization_edit", methods="GET|POST")
     * @IsGranted("ROLE_ADMIN")
     *  @param Organization $organization
     * @param Request $request
     * @return Response
     */
    public function editOrganization(Organization $organization, Request $request): Response
    {
        $form = ($this->createForm(OrganizationType::class, $organization))
            ->handleRequest($request);

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
