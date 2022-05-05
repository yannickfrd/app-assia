<?php

declare(strict_types=1);

namespace App\Controller\Organization;

use App\Entity\Organization\Service;
use App\Entity\Organization\SubService;
use App\Form\Organization\SubService\SubServiceType;
use App\Repository\Organization\PlaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class SubServiceController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/service/{id}/sub-service/new", name="sub_service_new", methods="GET|POST")
     */
    public function new(Service $service, Request $request): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $service);

        $form = $this->createForm(SubServiceType::class, $subService = new SubService())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $subService->setService($service);

            $this->em->persist($subService);
            $this->em->flush();

            $this->addFlash('success', 'Le sous-service est créé.');

            $this->deleteCacheItems($subService->getService());

            return $this->redirectToRoute('service_edit', ['id' => $service->getId()]);
        }

        return $this->render('app/organization/sub_service/sub_service.html.twig', [
            'service' => $service,
                'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/sub-service/{id}", name="sub_service_edit", methods="GET|POST")
     */
    public function edit(SubService $subService, PlaceRepository $placeRepo, Request $request): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $subService->getService());

        $form = $this->createForm(SubServiceType::class, $subService)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted('EDIT', $subService->getService());

            $this->em->flush();

            $this->deleteCacheItems($subService->getService());

            $this->addFlash('success', 'Les modifications sont enregistrées.');
        }

        $places = $placeRepo->findPlacesOfSubService($subService);

        $nbPlaces = 0;
        foreach ($places as $place) {
            $nbPlaces += $place->getNbPlaces();
        }

        return $this->render('app/organization/sub_service/sub_service.html.twig', [
            'service' => $subService->getService(),
            'form' => $form->createView(),
            // 'users' => $userRepo->findUsersFromSubService($subService),
            'places' => $places,
            'nbPlaces' => $nbPlaces,
        ]);
    }

    /**
     * @Route("/sub-service/{id}/disable", name="sub_service_disable", methods="GET")
     */
    public function disable(SubService $subService): Response
    {
        $this->denyAccessUnlessGranted('DISABLE', $subService->getService());

        if ($subService->getDisabledAt()) {
            $subService->setDisabledAt(null);
            $this->addFlash('success', 'Le sous-service "'.$subService->getName().'" est ré-activé.');
        } else {
            $subService->setDisabledAt(new \DateTime());
            $this->addFlash('warning', 'Le sous-service "'.$subService->getName().'" est désactivé.');
        }

        $this->deleteCacheItems($subService->getService());

        $this->em->flush();

        return $this->redirectToRoute('service_edit', ['id' => $subService->getService()->getId()]);
    }

    protected function deleteCacheItems(Service $service): bool
    {
        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        return $cache->deleteItem(Service::CACHE_SERVICE_SUBSERVICES_KEY.$service->getId());
    }
}
