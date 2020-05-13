<?php

namespace App\Controller;

use App\Entity\Service;
use App\Export\ServiceExport;
use App\Form\Model\ServiceSearch;
use App\Form\Service\ServiceSearchType;
use App\Form\Service\ServiceType;
use App\Repository\AccommodationRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use App\Service\Pagination;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function listServices(Request $request, ServiceSearch $search = null, Pagination $pagination): Response
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
            'services' => $pagination->paginate($this->repo->findAllServicesQuery($search), $request) ?? null,
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
    public function editService(int $id, UserRepository $repoUser, AccommodationRepository $repoAccommodation, Request $request): Response
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
            'users' => $repoUser->findUsersFromService($service),
            'accommodations' => $accommodations,
            'nbPlaces' => $nbPlaces,
        ]);
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
