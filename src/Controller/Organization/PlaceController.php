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
use App\Service\Export\PlaceExport;
use App\Service\Pagination;
use App\Service\Place\PlaceManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

final class PlaceController extends AbstractController
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/places", name="place_index", methods="GET|POST")
     */
    public function index(Request $request, PlaceRepository $placeRepo, Pagination $pagination): Response
    {
        $form = $this->createForm(PlaceSearchType::class, $search = new PlaceSearch())
            ->handleRequest($request);

        if ($search->getExport()) {
            $places = $placeRepo->findPlacesToExport($search);

            if ($places) {
                return (new PlaceExport())->exportData($places);
            }
            $this->addFlash('warning', 'no_result_to_export');
        }

        return $this->renderForm('app/organization/place/place_index.html.twig', [
            'placeSearch' => $search,
            'form' => $form,
            'places' => $pagination->paginate($placeRepo->findPlacesQuery($search, $this->getUser()), $request),
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

            $this->addFlash('success', $this->translator->trans('place.created_successfully',
                ['place_name' => $place->getName()], 'app')
            );

            PlaceManager::deleteCacheItems($place);

            return $this->redirectToRoute('service_edit', ['id' => $service->getId()]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('danger', 'error_occurred');
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

            $this->addFlash('success', $this->translator->trans('place.updated_successfully',
                ['place_name' => $place->getName()], 'app')
            );
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

        $this->addFlash('warning', $this->translator->trans('place.deleted_successfully', [
            'place_name' => $place->getName(),
        ], 'app'));

        PlaceManager::deleteCacheItems($place);

        return $this->redirectToRoute('service_edit', ['id' => $place->getService()->getId()]);
    }

    /**
     * @Route("/place/{id}/disable", name="place_disable", methods="GET")
     */
    public function disable(Place $place, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('DISABLE', $place);

        $isDisabled = $place->isDisabled();

        $place->setDisabledAt($isDisabled ? null : new \DateTime());

        $this->addFlash('success', $this->translator->trans(
            $isDisabled ? 'place.actived_successfully' : 'place.disabled_successfully', [
                'place_name' => $place->getName(),
        ], 'app'));

        PlaceManager::deleteCacheItems($place);

        $em->flush();

        return $this->redirectToRoute('place_edit', ['id' => $place->getId()]);
    }
}
