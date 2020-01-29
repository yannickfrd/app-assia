<?php

namespace App\Controller;

use App\Entity\Place;

use App\Entity\Service;

use App\Form\Service\PlaceType;

use App\Repository\PlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PlaceController extends AbstractController
{
    private $manager;
    private $repo;
    private $security;

    public function __construct(EntityManagerInterface $manager, PlaceRepository $repo, Security $security)
    {
        $this->manager = $manager;
        $this->repo = $repo;
        $this->security = $security;
    }

    /**
     * Rechercher un groupe de places
     * 
     * @Route("/admin/places", name="admin_places")
     * @return Response
     */
    public function listPlaces(Request $request, PaginatorInterface $paginator): Response
    {
        $places =  $paginator->paginate(
            $this->repo->findAllPlacesQuery(),
            $request->query->getInt("page", 1), // page number
            20 // limit per page
        );
        $places->setCustomParameters([
            "align" => "right", // alignement de la pagination
        ]);

        return $this->render("app/admin/listPlaces.html.twig", [
            "places" => $places ?? null
        ]);
    }

    /**
     * Créer un groupe de places
     * 
     * @Route("/service/{id}/place/new", name="service_place_new", methods="GET|POST")
     *  @return Response
     */
    public function createPlace(Service $service, Place $place = null, Request $request): Response
    {
        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

        $place = new Place();
        $place->setService($service);

        $form = $this->createForm(PlaceType::class, $place);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $this->security->getUser();

            $place->setCreatedAt(new \DateTime())
                ->setCreatedBy($user)
                ->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($user);

            $this->manager->persist($place);
            $this->manager->flush();

            $this->addFlash("success", "Le groupe de places a été créé.");

            return $this->redirectToRoute("service_edit", ["id" => $service->getId()]);
        }
        return $this->render("app/admin/place.html.twig", [
            "form" => $form->createView(),
            "service" => $service,
            "edit_mode" => false
        ]);
    }

    /**
     * Editer la fiche du groupe de places
     * 
     * @Route("/admin/place/{id}", name="admin_place_edit", methods="GET|POST")
     *  @return Response
     */
    public function editPlace(Place $place, Request $request): Response
    {
        $this->denyAccessUnlessGranted("ROLE_SUPER_ADMIN");

        $form = $this->createForm(PlaceType::class, $place);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $place->setUpdatedAt(new \DateTime())
                ->setUpdatedBy($this->security->getUser());

            $this->manager->flush();

            $this->addFlash("success", "Les modifications ont été enregistrées.");
            return $this->redirectToRoute("admin_places");
        }
        return $this->render("app/admin/place.html.twig", [
            "form" => $form->createView(),
            "service" => $place->getService(),
            "edit_mode" => true
        ]);
    }
}
