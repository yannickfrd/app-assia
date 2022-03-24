<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use App\Entity\Organization\Organization;
use App\Form\Organization\Organization\OrganizationType;
use App\Repository\Organization\OrganizationRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class OrganizationController extends AbstractController
{
    private $em;
    private $organizationRepo;

    public function __construct(EntityManagerInterface $em, OrganizationRepository $organizationRepo)
    {
        $this->em = $em;
        $this->organizationRepo = $organizationRepo;
    }

    /**
     * Affiche la liste des dispositifs.
     *
     * @Route("/organizations", name="organizations", methods="GET|POST")
     */
    public function listOrganization(Request $request, Pagination $pagination): Response
    {
        return $this->render('app/organization/organization/listOrganizations.html.twig', [
            'organizations' => $pagination->paginate($this->organizationRepo->findOrganizationsQuery(), $request) ?? null,
        ]);
    }

    /**
     * Nouveau dispositif.
     *
     * @Route("/admin/organization/new", name="admin_organization_new", methods="GET|POST")
     */
    public function newOrganization(Request $request): Response
    {
        $form = $this->createForm(OrganizationType::class, $organization = new Organization())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($organization);
            $this->em->flush();

            $this->addFlash('success', 'Le dispositif est créé.');

            return $this->redirectToRoute('organizations');
        }

        return $this->render('app/organization/organization/organization.html.twig', [
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
        $form = $this->createForm(OrganizationType::class, $organization)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            $this->addFlash('success', 'Les modifications sont enregistrées.');
        }

        return $this->render('app/organization/organization/organization.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
