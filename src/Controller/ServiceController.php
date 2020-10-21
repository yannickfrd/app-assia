<?php

namespace App\Controller;

use App\Entity\Service;
use App\Service\Pagination;
use App\Export\ServiceExport;
use App\Form\Model\ServiceSearch;
use App\Form\Service\ServiceType;
use App\Repository\UserRepository;
use App\Repository\ServiceRepository;
use App\Form\Service\ServiceSearchType;
use App\Repository\SubServiceRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\AccommodationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ServiceController extends AbstractController
{
    private $manager;
    private $repo;

    public function __construct(EntityManagerInterface $manager, ServiceRepository $repo)
    {
        $this->manager = $manager;
        $this->repo = $repo;
    }

    /**
     * Liste des services.
     *
     * @Route("/services", name="services", methods="GET")
     */
    public function listServices(Request $request, Pagination $pagination): Response
    {
        $search = new ServiceSearch();

        $form = ($this->createForm(ServiceSearchType::class, $search))
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search);
        }

        return $this->render('app/service/listServices.html.twig', [
            'serviceSearch' => $search,
            'form' => $form->createView(),
            'services' => $pagination->paginate($this->repo->findAllServicesQuery($search, $this->getUser()), $request) ?? null,
        ]);
    }

    /**
     * Nouveau service.
     *
     * @Route("/service/new", name="service_new", methods="GET|POST")
     * @IsGranted("ROLE_SUPER_ADMIN")
     */
    public function newService(Service $service = null, Request $request): Response
    {
        $service = new Service();

        $form = ($this->createForm(ServiceType::class, $service))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->manager->persist($service);
            $this->manager->flush();

            $this->addFlash('success', 'Le service est créé.');

            return $this->redirectToRoute('service_edit', ['id' => $service->getId()]);
        }

        return $this->render('app/service/service.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Modification d'un service.
     *
     * @Route("/service/{id}", name="service_edit", methods="GET|POST")
     *
     * @param int $id from Service
     */
    public function editService(int $id, SubServiceRepository $repoSubService, UserRepository $repoUser, AccommodationRepository $repoAccommodation, Request $request): Response
    {
        $service = $this->repo->getFullService($id);

        $this->denyAccessUnlessGranted('VIEW', $service);

        $form = ($this->createForm(ServiceType::class, $service))
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted('EDIT', $service);

            $this->manager->flush();

            $this->addFlash('success', 'Les modifications sont enregistrées.');
        }

        $accommodations = $repoAccommodation->findAccommodationsFromService($service);

        $nbPlaces = 0;
        foreach ($accommodations as $accommodation) {
            $nbPlaces += $accommodation->getNbPlaces();
        }

        return $this->render('app/service/service.html.twig', [
            'form' => $form->createView(),
            'subServices' => $repoSubService->findSubServicesFromService($service),
            'users' => $repoUser->findUsersFromService($service),
            'accommodations' => $accommodations,
            'nbPlaces' => $nbPlaces,
        ]);
    }

    /**
     * Désactive ou réactive le service.
     *
     * @Route("/service/{id}/disable", name="service_disable", methods="GET")
     */
    public function disableService(Service $service): Response
    {
        $this->denyAccessUnlessGranted('DISABLE', $service);

        if ($service->getDisabledAt()) {
            $service->setDisabledAt(null);
            $this->addFlash('success', 'Le service "'.$service->getName().'" est réactivé.');
        } else {
            $service->setDisabledAt(new \DateTime());
            $this->addFlash('warning', 'Le service "'.$service->getName().'" désactivé.');
        }

        $this->manager->flush();

        return $this->redirectToRoute('service_edit', ['id' => $service->getId()]);
    }

    /**
     * Exporte les données.
     */
    protected function exportData(ServiceSearch $search)
    {
        $services = $this->repo->findServicesToExport($search);

        return (new ServiceExport())->exportData($services);
    }
}
