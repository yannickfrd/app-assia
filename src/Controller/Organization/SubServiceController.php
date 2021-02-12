<?php

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

class SubServiceController extends AbstractController
{
    private $manager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Nouveau sous-service.
     *
     * @Route("/service/{id}/sub-service/new", name="sub_service_new", methods="GET|POST")
     */
    public function newSubService(Service $service, Request $request): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $service);

        $subService = new SubService();

        $form = ($this->createForm(SubServiceType::class, $subService))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $subService->setService($service);

            $this->manager->persist($subService);
            $this->manager->flush();

            $this->addFlash('success', 'Le sous-service est créé.');

            $this->discache($subService->getService());

            return $this->redirectToRoute('service_edit', ['id' => $service->getId()]);
        }

        return $this->render('app/organization/subService/subServiceEdit.html.twig', [
            'service' => $service,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification d'un sous-service.
     *
     * @Route("/sub-service/{id}", name="sub_service_edit", methods="GET|POST")
     */
    public function editSubService(SubService $subService, PlaceRepository $repoPlace, Request $request): Response
    {
        $this->denyAccessUnlessGranted('EDIT', $subService->getService());

        $form = ($this->createForm(SubServiceType::class, $subService))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted('EDIT', $subService->getService());

            $this->manager->flush();

            $this->discache($subService->getService());

            $this->addFlash('success', 'Les modifications sont enregistrées.');
        }

        $places = $repoPlace->findPlacesOfSubService($subService);

        $nbPlaces = 0;
        foreach ($places as $place) {
            $nbPlaces += $place->getNbPlaces();
        }

        return $this->render('app/organization/subService/subServiceEdit.html.twig', [
            'service' => $subService->getService(),
            'form' => $form->createView(),
            // 'users' => $repoUser->findUsersFromSubService($subService),
            'places' => $places,
            'nbPlaces' => $nbPlaces,
        ]);
    }

    /**
     * Désactive ou réactive le sous-service.
     *
     * @Route("/sub-service/{id}/disable", name="sub_service_disable", methods="GET")
     */
    public function disableSubService(SubService $subService): Response
    {
        $this->denyAccessUnlessGranted('DISABLE', $subService->getService());

        if ($subService->getDisabledAt()) {
            $subService->setDisabledAt(null);
            $this->addFlash('success', 'Le sous-service "'.$subService->getName().'" est réactivé.');
        } else {
            $subService->setDisabledAt(new \DateTime());
            $this->addFlash('warning', 'Le sous-service "'.$subService->getName().'" est désactivé.');
        }

        $this->discache($subService->getService());

        $this->manager->flush();

        return $this->redirectToRoute('service_edit', ['id' => $subService->getService()->getId()]);
    }

    /**
     * Supprime les sous-services en cache du service.
     */
    protected function discache(Service $service): bool
    {
        $cache = new FilesystemAdapter($_SERVER['DB_DATABASE_NAME']);

        return $cache->deleteItem(Service::CACHE_SERVICE_SUBSERVICES_KEY.$service->getId());
    }
}
