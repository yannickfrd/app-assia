<?php

declare(strict_types=1);

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
use App\Service\Place\PlaceManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class PlaceController extends AbstractController
{
    /**
     * @Route("/places", name="place_index", methods="GET|POST")
     */
    public function index(Request $request, PlaceRepository $placeRepo, Pagination $pagination,
        CurrentUserService $currentUser): Response
    {
        $form = $this->createForm(PlaceSearchType::class, $search = new PlaceSearch())
            ->handleRequest($request);

        if ($search->getExport()) {
            $places = $placeRepo->findPlacesToExport($search);

            if ($places) {
                return (new PlaceExport())->exportData($places);
            }
            $this->addFlash('warning', 'Aucun résultat à exporter.');
        }

        return $this->renderForm('app/organization/place/place_index.html.twig', [
            'placeSearch' => $search,
            'form' => $form,
            'places' => $pagination->paginate($placeRepo->findPlacesQuery($search, $currentUser), $request),
        ]);
    }

    /**
     * @Route("/admin/service/{id}/place/new", name="service_place_new", methods="GET|POST")
     */
    public function new(Service $service, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $service);

        $place = (new Place())->setService($service);

        $form = $this->createForm(PlaceType::class, $place)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($place);
            $em->flush();

            $this->addFlash('success', 'Le groupe de places est créé.');

            PlaceManager::deleteCacheItems($place);

            return $this->redirectToRoute('service_edit', ['id' => $service->getId()]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', "Une erreur s'est produite.");
        }

        return $this->renderForm('app/organization/place/place.html.twig', ['form' => $form]);
    }

    /**
     * @Route("/place/{id}", name="place_edit", methods="GET|POST")
     */
    public function edit(Place $place, Request $request, EntityManagerInterface $em,
        PlaceGroupRepository $placeGroupRepo): Response
    {
        $this->denyAccessUnlessGranted('VIEW', $place);

        $form = $this->createForm(PlaceType::class, $place)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted('EDIT', $place->getService());

            $em->flush();

            PlaceManager::deleteCacheItems($place);

            $this->addFlash('success', 'Les modifications sont enregistrées.');
        }

        return $this->renderForm('app/organization/place/place.html.twig', [
            'form' => $form,
            'places_group' => $placeGroupRepo->findAllPlaceGroups($place),
        ]);
    }

    /**
     * @Route("/place/{id}/delete", name="place_delete", methods="GET")
     * @IsGranted("DELETE", subject="place")
     */
    public function delete(Place $place, EntityManagerInterface $em): Response
    {
        $em->remove($place);
        $em->flush();

        $this->addFlash('warning', 'Le groupe de places est supprimé.');

        PlaceManager::deleteCacheItems($place);

        return $this->redirectToRoute('service_edit', ['id' => $place->getService()->getId()]);
    }

    /**
     * @Route("/place/{id}/disable", name="place_disable", methods="GET")
     */
    public function disable(Place $place, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('DISABLE', $place);

        if ($place->getDisabledAt()) {
            $place->setDisabledAt(null);
            $this->addFlash('success', 'Le groupe de places "'.$place->getName().'" est ré-activé.');
        } else {
            $place->setDisabledAt(new \DateTime());
            $this->addFlash('warning', 'Le groupe de places "'.$place->getName().'" est désactivé.');
        }

        PlaceManager::deleteCacheItems($place);

        $em->flush();

        return $this->redirectToRoute('place_edit', ['id' => $place->getId()]);
    }
}
