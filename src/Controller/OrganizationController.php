<?php

namespace App\Controller;

use App\Entity\Organization;
use App\Form\Organization\OrganizationType;
use App\Repository\OrganizationRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     * Affiche la liste des dispositifs.
     *
     * @Route("/organizations", name="organizations", methods="GET|POST")
     */
    public function listOrganization(Request $request, Pagination $pagination): Response
    {
        return $this->render('app/organization/listOrganizations.html.twig', [
            'organizations' => $pagination->paginate($this->repo->findAllOrganizationsQuery(), $request) ?? null,
        ]);
    }

    /**
     * Nouveau dispositif.
     *
     * @Route("/admin/organization/new", name="admin_organization_new", methods="GET|POST")
     */
    public function newOrganization(Organization $organization = null, Request $request): Response
    {
        $organization = new Organization();

        $form = ($this->createForm(OrganizationType::class, $organization))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($organization);
            $this->manager->flush();

            $this->addFlash('success', 'Le dispositif est créé.');

            return $this->redirectToRoute('organizations');
        }

        return $this->render('app/organization/organization.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification d'un dispositif.
     *
     * @Route("/admin/organization/{id}", name="admin_organization_edit", methods="GET|POST")
     */
    public function editOrganization(Organization $organization, Request $request): Response
    {
        $form = ($this->createForm(OrganizationType::class, $organization))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->flush();

            $this->addFlash('success', 'Les modifications sont enregistrées.');
        }

        return $this->render('app/organization/organization.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
