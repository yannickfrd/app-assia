<?php

namespace App\Controller;

use App\Entity\Service;
use App\Service\Pagination;
use App\Entity\Accommodation;
use App\Form\Model\AccommodationSearch;
use App\Form\Service\AccommodationType;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AccommodationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\Accommodation\AccommodationSearchType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccommodationController extends AbstractController
{
    private $manager;
    private $repo;

    public function __construct(EntityManagerInterface $manager, AccommodationRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
    }

    /**
     * Affiche la liste des groupes de places
     * 
     * @Route("/admin/accommodations", name="admin_accommodations")
     * @param AccommodationSearch $accommodationSearch
     * @param Request $request
     * @return Response
     */
    public function listAccommodations(AccommodationSearch $accommodationSearch, Request $request, Pagination $pagination): Response
    {
        $accommodationSearch = new AccommodationSearch();

        $form = $this->createForm(AccommodationSearchType::class, $accommodationSearch);
        $form->handleRequest($request);

        $accommodations = $pagination->paginate($this->repo->findAllAccommodationsQuery($accommodationSearch), $request);

        return $this->render("app/admin/listAccommodations.html.twig", [
            "accommodations" => $accommodations ?? null,
            "accommodationSearch" => $accommodationSearch,
            "form" => $form->createView()
        ]);
    }

    /**
     * Nouveau groupe de places
     * 
     * @Route("/admin/service/{id}/accommodation/new", name="service_accommodation_new", methods="GET|POST")
     * @param Service $service
     * @param Accommodation $accommodation
     * @param Request $request
     * @return Response
     */
    public function newAccommodation(Service $service, Accommodation $accommodation = null, Request $request): Response
    {
        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

        $accommodation = new Accommodation();
        $accommodation->setService($service);

        $form = $this->createForm(AccommodationType::class, $accommodation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            return $this->createAccommodation($accommodation, $service);
        }
        return $this->render("app/admin/accommodation.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => false
        ]);
    }

    /**
     * Modification d'un groupe de places
     * 
     * @Route("/admin/accommodation/{id}", name="admin_accommodation_edit", methods="GET|POST")
     * @param Accommodation $accommodation
     * @param Request $request
     * @return Response
     */
    public function editAccommodation(Accommodation $accommodation, Request $request): Response
    {
        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

        $form = $this->createForm(AccommodationType::class, $accommodation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            return $this->updateAccommodation($accommodation);
        }
        return $this->render("app/admin/accommodation.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => true
        ]);
    }

    /**
     * Supprime le groupe de places
     * 
     * @Route("admin/accommodation/{id}/delete", name="admin_accommodation_delete")
     * @param Accommodation $accommodation
     * @return Response
     */
    public function deleteAccommodation(Accommodation $accommodation): Response
    {
        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

        $this->manager->remove($accommodation);
        $this->manager->flush();

        $this->addFlash("danger", "Le groupe de places a été supprimé.");

        return $this->redirectToRoute("service_edit", ["id" => $accommodation->getService()->getId()]);
    }

    /**
     * Crée un groupe de places
     *
     * @param Accommodation $accommodation
     * @param Service $service
     */
    protected function createAccommodation(Accommodation $accommodation, Service $service)
    {
        $now = new \DateTime();

        $accommodation->setCreatedAt($now)
            ->setCreatedBy($this->getUser())
            ->setUpdatedAt($now)
            ->setUpdatedBy($this->getUser());

        $this->manager->persist($accommodation);
        $this->manager->flush();

        $this->addFlash("success", "Le groupe de places a été créé.");

        return $this->redirectToRoute("service_edit", ["id" => $service->getId()]);
    }

    /**
     * Met à jour un groupe de place
     *
     * @param Accommodation $accommodation
     */
    protected function updateAccommodation(Accommodation $accommodation)
    {
        $accommodation->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->getUser());

        $this->manager->flush();

        $this->addFlash("success", "Les modifications ont été enregistrées.");

        return $this->redirectToRoute("service_edit", ["id" => $accommodation->getService()->getId()]);
    }
}
