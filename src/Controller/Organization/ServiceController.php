<?php

namespace App\Controller\Organization;

use App\Service\Pagination;
use Psr\Cache\CacheItemInterface;
use App\Entity\Organization\Service;
use App\Service\Export\ServiceExport;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\Model\Organization\ServiceSearch;
use App\Form\Organization\Service\ServiceType;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\Organization\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\Organization\ServiceRepository;
use App\Form\Organization\Service\ServiceSearchType;
use App\Repository\Organization\SubServiceRepository;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use App\Repository\Organization\AccommodationRepository;
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

        return $this->render('app/organization/service/listServices.html.twig', [
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
    public function newService(Request $request): Response
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

        return $this->render('app/organization/service/service.html.twig', [
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

        $cache = new FilesystemAdapter();

        $accommodations = $this->getAccommodations($service, $repoAccommodation, $cache);

        $nbPlaces = 0;
        foreach ($accommodations as $accommodation) {
            $nbPlaces += $accommodation->getNbPlaces();
        }

        return $this->render('app/organization/service/service.html.twig', [
            'form' => $form->createView(),
            'subServices' => $this->getSubServices($service, $repoSubService, $cache),
            'users' => $this->getUsers($service, $repoUser, $cache),
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

    protected function getSubServices(Service $service, SubServiceRepository $repoSubService, FilesystemAdapter $cache)
    {
        return $repoSubService->findSubServicesOfService($service);

        return $cache->get(Service::CACHE_SERVICE_SUBSERVICES_KEY.$service->getId(), function (CacheItemInterface $item) use ($service, $repoSubService) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 month'));

            return $repoSubService->findSubServicesOfService($service);
        });
    }

    protected function getAccommodations(Service $service, AccommodationRepository $repoAccommodation, FilesystemAdapter $cache)
    {
        return $repoAccommodation->findAccommodationsOfService($service);

        return $cache->get(Service::CACHE_SERVICE_ACCOMMODATIONS_KEY.$service->getId(), function (CacheItemInterface $item) use ($service, $repoAccommodation) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 month'));

            return $repoAccommodation->findAccommodationsOfService($service);
        });
    }

    protected function getUsers(Service $service, UserRepository $repoUser, FilesystemAdapter $cache)
    {
        return $repoUser->findUsersOfService($service);

        return $cache->get(Service::CACHE_SERVICE_USERS_KEY.$service->getId(), function (CacheItemInterface $item) use ($service, $repoUser) {
            $item->expiresAfter(\DateInterval::createFromDateString('1 month'));

            return $repoUser->findUsersOfService($service);
        });
    }
}
