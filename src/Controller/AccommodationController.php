<?php

namespace App\Controller;

use App\Entity\Service;
use App\Entity\Accommodation;
use App\Form\Model\AccommodationSearch;
use App\Form\Service\AccommodationType;
use App\Form\User\AccommodationSearchType;
use App\Repository\AccommodationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccommodationController extends AbstractController
{
    private $manager;
    private $currentUser;
    private $repo;

    public function __construct(EntityManagerInterface $manager, Security $security, AccommodationRepository $repo)
    {
        $this->manager = $manager;
        $this->currentUser = $security->getUser();
        $this->repo = $repo;
    }

    /**
     * Affiche la liste des groupes de places
     * 
     * @Route("/admin/accommodations", name="admin_accommodations")
     * @param AccommodationSearch $accommodationSearch
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @return Response
     */
    public function listAccommodations(AccommodationSearch $accommodationSearch, Request $request, PaginatorInterface $paginator): Response
    {
        $accommodationSearch = new AccommodationSearch();

        $form = $this->createForm(AccommodationSearchType::class, $accommodationSearch);
        $form->handleRequest($request);

        $accommodations = $this->paginate($paginator, $accommodationSearch, $request);

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
     * Pagination
     *
     * @param PaginatorInterface $paginator
     * @param AccommodationSearch $accommodationSearch
     * @param Request $request
     */
    protected function paginate(PaginatorInterface $paginator, AccommodationSearch $accommodationSearch, Request $request)
    {
        $accommodations =  $paginator->paginate(
            $this->repo->findAllAccommodationsQuery($accommodationSearch),
            $request->query->getInt("page", 1), // page number
            20 // limit per page
        );

        $accommodations->setCustomParameters([
            "align" => "right", // alignement de la pagination
        ]);

        return $accommodations;
    }
    /**
     * Crée un groupe de places
     *
     * @param Accommodation $accommodation
     * @param Service $service
     * @return void
     */
    protected function createAccommodation(Accommodation $accommodation, Service $service)
    {
        $now = new \DateTime();

        $accommodation->setCreatedAt($now)
            ->setCreatedBy($this->currentUser)
            ->setUpdatedAt($now)
            ->setUpdatedBy($this->currentUser);

        $this->manager->persist($accommodation);
        $this->manager->flush();

        $this->addFlash("success", "Le groupe de places a été créé.");

        return $this->redirectToRoute("service_edit", ["id" => $service->getId()]);
    }

    /**
     * Met à jour un groupe de place
     *
     * @param Accommodation $accommodation
     * @return void
     */
    protected function updateAccommodation(Accommodation $accommodation)
    {
        $accommodation->setUpdatedAt(new \DateTime())
            ->setUpdatedBy($this->currentUser);

        $this->manager->flush();

        $this->addFlash("success", "Les modifications ont été enregistrées.");

        return $this->redirectToRoute("service_edit", ["id" => $accommodation->getService()->getId()]);
    }
}
