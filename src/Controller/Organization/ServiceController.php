<?php

namespace App\Controller\Organization;

use App\Entity\Organization\Service;
use App\Form\Model\Organization\ServiceSearch;
use App\Form\Organization\Service\ServiceSearchType;
use App\Form\Organization\Service\ServiceType;
use App\Repository\Organization\PlaceRepository;
use App\Repository\Organization\ServiceRepository;
use App\Repository\Organization\SubServiceRepository;
use App\Repository\Organization\UserRepository;
use App\Service\Export\ServiceExport;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ServiceController extends AbstractController
{
    private $serviceRepo;
    private $em;

    public function __construct(ServiceRepository $serviceRepo, EntityManagerInterface $em)
    {
        $this->serviceRepo = $serviceRepo;
        $this->em = $em;
    }

    /**
     * Liste des services.
     *
     * @Route("/services", name="services", methods="GET")
     */
    public function listServices(Request $request, Pagination $pagination): Response
    {
        $form = $this->createForm(ServiceSearchType::class, $search = new ServiceSearch())
            ->handleRequest($request);

        if ($search->getExport()) {
            return $this->exportData($search);
        }

        return $this->render('app/organization/service/listServices.html.twig', [
            'serviceSearch' => $search,
            'form' => $form->createView(),
            'services' => $pagination->paginate($this->serviceRepo->findServicesQuery($search, $this->getUser()), $request) ?? null,
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
        $form = $this->createForm(ServiceType::class, $service = new Service())
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($service);
            $this->em->flush();

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
    public function showService(
        int $id,
        Request $request,
        SubServiceRepository $subServiceRepo,
        UserRepository $userRepo,
        PlaceRepository $placeRepo
    ): Response {
        $service = $this->serviceRepo->getFullService($id);

        $this->denyAccessUnlessGranted('VIEW', $service);

        $form = $this->createForm(ServiceType::class, $service)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->denyAccessUnlessGranted('EDIT', $service);

            $this->em->flush();

            $this->addFlash('success', 'Les modifications sont enregistrées.');
        }

        $places = $placeRepo->findPlacesOfService($service);

        $nbPlaces = 0;
        foreach ($places as $place) {
            $nbPlaces += $place->getNbPlaces();
        }

        return $this->render('app/organization/service/service.html.twig', [
            'form' => $form->createView(),
            'subServices' => $subServiceRepo->findSubServicesOfService($service),
            'users' => $userRepo->findUsersOfService($service),
            'places' => $places,
            'nbPlaces' => $nbPlaces,
        ]);
    }

    /**
     * Désactive ou réactive le service.
     *
     * @Route("/admin/service/{id}/disable", name="service_disable", methods="GET")
     */
    public function disableService(Service $service): Response
    {
        $this->denyAccessUnlessGranted('DISABLE', $service);

        if ($service->getDisabledAt()) {
            $service->setDisabledAt(null);
            $this->addFlash('success', 'Le service "'.$service->getName().'" est ré-activé.');
        } else {
            $service->setDisabledAt(new \DateTime());
            $this->addFlash('warning', 'Le service "'.$service->getName().'" est désactivé.');
        }

        $this->em->flush();

        return $this->redirectToRoute('service_edit', ['id' => $service->getId()]);
    }

    /**
     * Exporte les données.
     */
    protected function exportData(ServiceSearch $search): Response
    {
        $services = $this->serviceRepo->findServicesToExport($search);

        return (new ServiceExport())->exportData($services);
    }
}
