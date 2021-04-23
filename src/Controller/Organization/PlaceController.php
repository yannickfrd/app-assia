<?php

namespace App\Controller\Organization;

use App\Entity\Organization\Place;
use App\Entity\Organization\Service;
use App\Form\Model\Organization\PlaceSearch;
use App\Form\Organization\Place\PlaceSearchType;
use App\Form\Organization\Place\PlaceType;
use App\Repository\Organization\PlaceRepository;
use App\Repository\Support\PlaceGroupRepository;
use App\Security\CurrentUserService;
use App\Service\Export\PlaceExport;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlaceController extends AbstractController
{
    private $manager;
    private $placeRepo;

    public function __construct(EntityManagerInterface $manager, PlaceRepository $placeRepo)
    {
        $this->manager = $manager;
        $this->placeRepo = $placeRepo;
    }

    /**
     * Affiche la liste des groupes de places.
     *
     * @Route("/places", name="places", methods="GET|POST")
     */
    public function listPlaces(Request $request, Pagination $pagination, CurrentUserService $currentUser): Response
    {
        $form = $this->createForm(PlaceSearchType::class, $search = new PlaceSearch())
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search);
        }

        return $this->render('app/organization/place/listPlaces.html.twig', [
            'placeSearch' => $search,
            'form' => $form->createView(),
            'places' => $pagination->paginate($this->placeRepo->findPlacesQuery($search, $currentUser), $request) ?? null,
        ]);
    }

    /**
     * Nouveau groupe de places.
     *
     * @Route("/admin/service/{id}/place/new", name="service_place_new", methods="GET|POST")
     */
    public function newPlace(Service $service, Place $place = null, Request $request): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $service);

        $place = (new Place())->setService($service);

        $form = $this->createForm(PlaceType::class, $place)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($place);
            $this->manager->flush();

            $this->addFlash('success', 'Le groupe de places est créé.');

            $this->discache($place->getService());

            return $this->redirectToRoute('service_edit', ['id' => $service->getId()]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', "Une erreur s'est produite.");
        }

        return $this->render('app/organization/place/place.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification d'un groupe de places.
     *
     * @Route("/place/{id}", name="place_edit", methods="GET|POST")
     */
    public function editPlace(Place $place, Request $request, PlaceGroupRepository $placeGroupRepo): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $place);

        $form = $this->createForm(PlaceType::class, $place)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted('EDIT', $place->getService());

            $this->manager->flush();

            $this->discache($place->getService());

            $this->addFlash('success', 'Les modifications sont enregistrées.');
        }

        return $this->render('app/organization/place/place.html.twig', [
            'form' => $form->createView(),
            'places_group' => $placeGroupRepo->findAllPlace($place),
        ]);
    }

    /**
     * Supprime le groupe de places.
     *
     * @Route("/admin/place/{id}/delete", name="admin_place_delete", methods="GET")
     * @IsGranted("DELETE", subject="place")
     */
    public function deletePlace(Place $place): Response
    {
        $this->manager->remove($place);
        $this->manager->flush();

        $this->addFlash('warning', 'Le groupe de places est supprimé.');

        $this->discache($place->getService());

        return $this->redirectToRoute('service_edit', ['id' => $place->getService()->getId()]);
    }

    /**
     * Désactive ou réactive le place.
     *
     * @Route("/admin/place/{id}/disable", name="admin_place_disable", methods="GET")
     */
    public function disablePlace(Place $place): Response
    {
        $this->denyAccessUnlessGranted('DISABLE', $place);

        if ($place->getDisabledAt()) {
            $place->setDisabledAt(null);
            $this->addFlash('success', 'Le groupe de place "'.$place->getName().'" est ré-activé.');
        } else {
            $place->setDisabledAt(new \DateTime());
            $this->addFlash('warning', 'Le groupe de place "'.$place->getName().'" est désactivé.');
        }

        $this->discache($place->getService());

        $this->manager->flush();

        return $this->redirectToRoute('place_edit', ['id' => $place->getId()]);
    }

    /**
     * Exporte les données.
     */
    protected function exportData(PlaceSearch $search)
    {
        $places = $this->placeRepo->findPlacesToExport($search);

        if (!$places) {
            $this->addFlash('warning', 'Aucun résultat à exporter.');

            return $this->redirectToRoute('supports');
        }

        return (new PlaceExport())->exportData($places);
    }

    /**
     * Supprime les groupes de places en cache du service.
     */
    protected function discache(Service $service): bool
    {
        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        return $cache->deleteItem(Service::CACHE_SERVICE_PLACES_KEY.$service->getId());
    }
}
