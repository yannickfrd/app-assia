<?php

namespace App\Controller;

use App\Entity\Service;

use App\Entity\Accommodation;
use App\Form\Model\AccommodationSearch;
use App\Form\Service\AccommodationType;

use Doctrine\ORM\EntityManagerInterface;
use App\Form\User\AccommodationSearchType;
use App\Repository\AccommodationRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AccommodationController extends AbstractController
{
    private $manager;
    private $repo;
    private $security;

    public function __construct(EntityManagerInterface $manager, AccommodationRepository $repo, Security $security)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->security = $security;
    }

    /**
     * Rechercher un groupe de places
     * 
     * @Route("/admin/accommodations", name="admin_accommodations")
     * @return Response
     */
    public function listAccommodations(Request $request, AccommodationSearch $accommodationSearch, PaginatorInterface $paginator): Response
    {
        $accommodationSearch = new AccommodationSearch();

        $form = $this->createForm(AccommodationSearchType::class, $accommodationSearch);
        $form->handleRequest($request);

        $accommodations =  $paginator->paginate(
            $this->repo->findAllAccommodationsQuery($accommodationSearch),
            $request->query->getInt("page", 1), // page number
            20 // limit per page
        );
        $accommodations->setCustomParameters([
            "align" => "right", // alignement de la pagination
        ]);

        return $this->render("app/admin/listAccommodations.html.twig", [
            "accommodations" => $accommodations ?? null,
            "accommodationSearch" => $accommodationSearch,
            "form" => $form->createView()

        ]);
    }

    /**
     * Créer un groupe de places
     * 
     * @Route("/admin/service/{id}/accommodation/new", name="service_accommodation_new", methods="GET|POST")
     *  @return Response
     */
    public function createAccommodation(Service $service, Accommodation $accommodation = null, Request $request): Response
    {
        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

        $accommodation = new Accommodation();
        $accommodation->setService($service);

        $form = $this->createForm(AccommodationType::class, $accommodation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->security->getUser();

            $accommodation->setCreatedAt(new \DateTime())
                ->setCreatedBy($user)
                ->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($user);

            $this->manager->persist($accommodation);
            $this->manager->flush();

            $this->addFlash("success", "Le groupe de places a été créé.");

            return $this->redirectToRoute("service_edit", ["id" => $service->getId()]);
        }
        return $this->render("app/admin/accommodation.html.twig", [
            "form" => $form->createView(),
            "edit_mode" => false
        ]);
    }

    /**
     * Editer la fiche du groupe de places
     * 
     * @Route("/admin/accommodation/{id}", name="admin_accommodation_edit", methods="GET|POST")
     *  @return Response
     */
    public function editAccommodation(Accommodation $accommodation, Request $request): Response
    {
        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

        $form = $this->createForm(AccommodationType::class, $accommodation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $accommodation->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->security->getUser());

            $this->manager->flush();

            $this->addFlash("success", "Les modifications ont été enregistrées.");
            return $this->redirectToRoute("service_edit", ["id" => $accommodation->getService()->getId()]);
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
}
